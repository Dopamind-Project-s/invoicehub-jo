<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\Audit\AuditLogger;
use App\Services\Invoices\InvoiceCalculator;
use App\Services\Invoices\InvoicePdfService;
use App\Services\Invoices\InvoiceBrandingService;
use App\Services\Invoices\InvoiceNotificationService;
use App\Services\Jofotara\JoFotaraApiService;
use App\Services\Jofotara\JoFotaraPreparationService;
use RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvoiceEngineController extends Controller
{
    public function __construct(private readonly InvoiceCalculator $calculator, private readonly AuditLogger $audit, private readonly InvoiceNotificationService $notifications, private readonly InvoiceBrandingService $branding) {}

    public function index(Request $request, Company $company)
    {
        $invoices = Invoice::with('contact')->where('company_id', $company->id)
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($s) => $s->where('invoice_number', 'like', '%'.$request->search.'%')->orWhereHas('contact', fn ($c) => $c->where('name_ar', 'like', '%'.$request->search.'%')->orWhere('tax_number', 'like', '%'.$request->search.'%'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('invoice_type'), fn ($q) => $q->where('invoice_type', $request->invoice_type))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->source))
            ->latest()->paginate(15)->withQueryString();

        return view('company.invoices.index', compact('company', 'invoices'));
    }

    public function create(Company $company)
    {
        return view('company.invoices.create', $this->formData($company, new Invoice([
            'status' => Invoice::STATUS_DRAFT,
            'invoice_type' => Invoice::TYPE_TAX_INVOICE,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'currency' => $company->default_currency ?: 'JOD',
        ])));
    }

    public function store(Request $request, Company $company)
    {
        $invoice = DB::transaction(function () use ($request, $company): Invoice {
            $data = $this->validated($request, $company);
            $invoice = new Invoice;
            $this->fillAndSave($invoice, $company, $data);
            $this->audit->record('invoice.created', $invoice, [], $invoice->load('items')->toArray(), $request);

            return $invoice;
        });

        return redirect()->route('company.invoices.show', [$company, $invoice])->with('status', $invoice->status === Invoice::STATUS_READY ? 'تم حفظ الفاتورة وتجهيزها للإرسال.' : 'تم حفظ الفاتورة كمسودة.');
    }

    public function show(Company $company, Invoice $invoice, JoFotaraPreparationService $preparer)
    {
        $this->authorizeCompany($company, $invoice);
        return view('company.invoices.show', ['company' => $company->loadMissing('featureKeys'), 'invoice' => $invoice->load(['contact', 'items.product', 'submissionLogs']), 'jofotaraDiagnostic' => $preparer->diagnostics($invoice)]);
    }

    public function jofotaraUat(Company $company, JoFotaraPreparationService $preparer)
    {
        abort_if(app()->environment('production'), 404);

        $lastAccepted = Invoice::where('company_id', $company->id)
            ->where('jofotara_status', 'ACCEPTED')
            ->whereNotNull('jofotara_uuid')
            ->whereNotNull('xml_hash')
            ->orderByDesc('icv')
            ->first();
        $lastFailed = Invoice::where('company_id', $company->id)
            ->whereIn('jofotara_status', ['ERROR', 'REJECTED'])
            ->latest('jofotara_submitted_at')
            ->first();
        $nextEligible = Invoice::where('company_id', $company->id)
            ->where('status', Invoice::STATUS_READY)
            ->whereNull('jofotara_status')
            ->orderBy('created_at')
            ->first();

        return view('company.invoices.jofotara-uat', [
            'company' => $company,
            'lastAccepted' => $lastAccepted,
            'lastFailed' => $lastFailed,
            'nextEligible' => $nextEligible,
            'diagnostic' => $nextEligible ? $preparer->diagnostics($nextEligible) : null,
        ]);
    }

    public function edit(Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_if($invoice->isReadOnly() || ! in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_READY], true), 403, 'يمكن تعديل المسودات والفواتير الجاهزة فقط.');
        return view('company.invoices.edit', $this->formData($company, $invoice->load('items')));
    }

    public function update(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_if($invoice->isReadOnly() || ! in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_READY], true), 403, 'يمكن تعديل المسودات والفواتير الجاهزة فقط.');
        DB::transaction(function () use ($request, $company, $invoice): void {
            $before = $invoice->load('items')->toArray();
            $this->fillAndSave($invoice, $company, $this->validated($request, $company, $invoice));
            $this->audit->record('invoice.edited', $invoice, $before, $invoice->load('items')->toArray(), $request);
        });

        return redirect()->route('company.invoices.show', [$company, $invoice])->with('status', $invoice->status === Invoice::STATUS_READY ? 'تم تحديث الفاتورة وتجهيزها للإرسال.' : 'تم تحديث الفاتورة كمسودة.');
    }

    public function submit(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_unless($invoice->status === Invoice::STATUS_DRAFT, 403, 'يمكن تجهيز المسودات فقط للإرسال.');
        $before = $invoice->only('status');
        $invoice->update(['status' => Invoice::STATUS_READY]);
        $this->audit->record('invoice.ready', $invoice, $before, $invoice->only('status'), $request);

        return back()->with('status', 'تم تجهيز الفاتورة للإرسال إلى نظام الفوترة الوطني.');
    }

    public function approve(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_unless($invoice->status === Invoice::STATUS_PENDING, 403, 'المراجعة الداخلية غير متاحة لهذه الفاتورة.');
        $before = $invoice->only('status', 'approved_by', 'approved_at');
        $invoice->update(['status' => Invoice::STATUS_READY, 'approved_by' => Auth::id(), 'approved_at' => now()]);
        $this->audit->record('invoice.approved', $invoice, $before, $invoice->only('status', 'approved_by', 'approved_at'), $request);
        $this->notifications->record($invoice, 'approved', Auth::id());

        return back()->with('status', 'تمت المراجعة الداخلية وأصبحت الفاتورة جاهزة للإرسال.');
    }

    public function cancel(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_unless(in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_READY, Invoice::STATUS_PENDING], true), 403, 'لا يمكن إلغاء هذه الفاتورة.');
        $before = $invoice->only('status');
        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);
        $this->audit->record('invoice.cancelled', $invoice, $before, $invoice->only('status'), $request);
        $this->notifications->record($invoice, 'cancelled', Auth::id());

        return back()->with('status', 'تم إلغاء الفاتورة.');
    }

    public function returnToDraft(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_unless($invoice->status === Invoice::STATUS_READY && blank($invoice->jofotara_status), 403, 'لا يمكن إرجاع هذه الفاتورة إلى مسودة.');
        $before = $invoice->only('status');
        $invoice->update(['status' => Invoice::STATUS_DRAFT]);
        $this->audit->record('invoice.returned_to_draft', $invoice, $before, $invoice->only('status'), $request);

        return back()->with('status', 'تم إرجاع الفاتورة إلى مسودة.');
    }

    public function submitToJofotara(Request $request, Company $company, Invoice $invoice, JoFotaraApiService $api)
    {
        $this->authorizeCompany($company, $invoice);
        abort_unless($this->canSubmitToJofotara($company, $invoice), 403, 'شروط الإرسال إلى جوفوتارا غير مكتملة.');

        $before = $invoice->only('jofotara_status', 'jofotara_uuid', 'jofotara_qr', 'jofotara_error_message');

        try {
            $result = $api->submit($invoice);
            $invoice->refresh();
            $invoice->forceFill(['status' => Invoice::STATUS_SUBMITTED])->save();
            $this->audit->record('invoice.jofotara.submitted', $invoice, $before, $invoice->only('jofotara_status', 'jofotara_uuid', 'jofotara_qr', 'jofotara_error_message'), $request);
            $this->notifications->record($invoice, 'submitted', Auth::id());

            return back()->with('status', 'تم إرسال الفاتورة إلى جوفوتارا. الحالة: '.$result['status']);
        } catch (RuntimeException $exception) {
            $invoice->forceFill(['jofotara_status' => 'ERROR', 'jofotara_error_message' => $exception->getMessage(), 'jofotara_submitted_at' => now()])->save();
            $this->audit->record('invoice.jofotara.failed', $invoice, $before, $invoice->only('jofotara_status', 'jofotara_error_message'), $request);

            return back()->withErrors(['jofotara' => $exception->getMessage()]);
        }
    }

    public function printable(Company $company, Invoice $invoice, InvoicePdfService $pdf)
    {
        $this->authorizeCompany($company, $invoice);
        return $pdf->download($invoice);
    }

    /** @param array<string, mixed> $data */
    private function fillAndSave(Invoice $invoice, Company $company, array $data): void
    {
        $totals = $this->calculator->calculate($data['items']);
        $invoice->forceFill([
            'company_id' => $company->id,
            'supplier_id' => $company->id,
            'contact_id' => $data['contact_id'],
            'customer_id' => null,
            'invoice_number' => $invoice->invoice_number ?: $this->nextInvoiceNumber($company),
            'uuid' => $invoice->uuid ?: (string) Str::uuid(),
            'icv' => $invoice->icv ?: $this->nextInternalIcv($company),
            'invoice_type' => $data['invoice_type'],
            'invoice_subtype' => match ($data['invoice_type']) { Invoice::TYPE_CREDIT_NOTE => 'CREDIT_NOTE', Invoice::TYPE_DEBIT_NOTE => 'DEBIT_NOTE', default => 'SALE' },
            'invoice_scope' => 'local',
            'payment_type' => 'receivable',
            'taxpayer_type' => 'income',
            'status' => ($data['save_action'] ?? 'draft') === 'ready' ? Invoice::STATUS_READY : ($invoice->status ?: Invoice::STATUS_DRAFT),
            'issue_date' => $data['issue_date'],
            'issue_time' => $invoice->issue_time ?: now()->format('H:i:s'),
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'currency' => $data['currency'],
            'currency_code' => $data['currency'],
            'exchange_rate' => '1.000000',
            'created_by' => $invoice->created_by ?: Auth::id(),
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount_total'],
            'discount_total' => $totals['discount_total'],
            'taxable_amount' => number_format((float) $totals['subtotal'] - (float) $totals['discount_total'], 6, '.', ''),
            'tax_amount' => $totals['tax_total'],
            'tax_total' => $totals['tax_total'],
            'total_amount' => $totals['grand_total'],
            'payable_amount' => $totals['grand_total'],
            'grand_total' => $totals['grand_total'],
        ])->save();

        $invoice->items()->delete();
        foreach ($totals['items'] as $line) {
            $invoice->items()->create($line);
        }
    }

    private function canSubmitToJofotara(Company $company, Invoice $invoice): bool
    {
        $company->loadMissing('featureKeys');

        return $company->featureKeys->contains('code', 'JOFOTARA_SUBMIT')
            && $company->hasJofotaraClientId()
            && $company->hasJofotaraSecretKey()
            && filled($company->jofotara_source_id)
            && ($company->is_active ?? true)
            && Auth::user()?->can('invoices.submit')
            && $invoice->status === Invoice::STATUS_READY
            && ! in_array($invoice->jofotara_status, ['ACCEPTED', 'SUBMITTED'], true);
    }

    private function authorizeCompany(Company $company, Invoice $invoice): void
    {
        abort_unless((int) $invoice->company_id === (int) $company->id, 404);
    }

    private function nextInvoiceNumber(Company $company): string
    {
        return 'INV-'.$company->id.'-'.now()->format('Y').'-'.str_pad((string) (Invoice::where('company_id', $company->id)->whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT);
    }

    private function nextInternalIcv(Company $company): int
    {
        return ((int) Invoice::max('icv')) + 1;
    }

    private function formData(Company $company, Invoice $invoice): array
    {
        return ['company' => $company, 'invoice' => $invoice, 'contacts' => Contact::where('company_id', $company->id)->where('is_active', true)->orderBy('name_ar')->get(), 'products' => Product::where('company_id', $company->id)->where('is_active', true)->orderBy('name_ar')->get(), 'branding' => $this->branding->settings($company), 'templates' => \App\Models\InvoiceTemplate::query()->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))->where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get()];
    }

    private function validated(Request $request, Company $company, ?Invoice $invoice = null): array
    {
        return $request->validate([
            'contact_id' => ['required', Rule::exists('contacts', 'id')->where('company_id', $company->id)],
            'invoice_type' => ['required', Rule::in([Invoice::TYPE_TAX_INVOICE, Invoice::TYPE_SIMPLIFIED_INVOICE, Invoice::TYPE_CREDIT_NOTE, Invoice::TYPE_DEBIT_NOTE])],
            'issue_date' => ['required', 'date'], 'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'], 'currency' => ['required', 'size:3'], 'notes' => ['nullable', 'string'],
            'save_action' => ['nullable', Rule::in(['draft', 'ready'])],
            'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('company_id', $company->id)], 'items.*.description' => ['required', 'string'], 'items.*.quantity' => ['required', 'numeric', 'gt:0'], 'items.*.unit_price' => ['required', 'numeric', 'min:0'], 'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'], 'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
