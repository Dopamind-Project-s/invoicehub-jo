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

        return redirect()->route('company.invoices.show', [$company, $invoice])->with('status', 'تم إنشاء مسودة الفاتورة.');
    }

    public function show(Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        return view('company.invoices.show', ['company' => $company, 'invoice' => $invoice->load(['contact', 'items.product'])]);
    }

    public function edit(Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_if($invoice->isReadOnly() || $invoice->status !== Invoice::STATUS_DRAFT, 403, 'يمكن تعديل المسودات فقط.');
        return view('company.invoices.edit', $this->formData($company, $invoice->load('items')));
    }

    public function update(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_if($invoice->isReadOnly() || $invoice->status !== Invoice::STATUS_DRAFT, 403, 'يمكن تعديل المسودات فقط.');
        DB::transaction(function () use ($request, $company, $invoice): void {
            $before = $invoice->load('items')->toArray();
            $this->fillAndSave($invoice, $company, $this->validated($request, $company, $invoice));
            $this->audit->record('invoice.edited', $invoice, $before, $invoice->load('items')->toArray(), $request);
        });

        return redirect()->route('company.invoices.show', [$company, $invoice])->with('status', 'تم تحديث الفاتورة.');
    }

    public function submit(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_unless($invoice->status === Invoice::STATUS_DRAFT, 403, 'يمكن إرسال المسودات فقط للاعتماد.');
        $before = $invoice->only('status');
        $invoice->update(['status' => Invoice::STATUS_PENDING]);
        $this->audit->record('invoice.submitted', $invoice, $before, $invoice->only('status'), $request);
        $this->notifications->record($invoice, 'submitted', Auth::id());

        return back()->with('status', 'تم إرسال الفاتورة للاعتماد.');
    }

    public function approve(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_unless($invoice->status === Invoice::STATUS_PENDING, 403, 'يمكن اعتماد الفواتير قيد الاعتماد فقط.');
        $before = $invoice->only('status', 'approved_by', 'approved_at');
        $invoice->update(['status' => Invoice::STATUS_APPROVED, 'approved_by' => Auth::id(), 'approved_at' => now()]);
        $this->audit->record('invoice.approved', $invoice, $before, $invoice->only('status', 'approved_by', 'approved_at'), $request);
        $this->notifications->record($invoice, 'approved', Auth::id());

        return back()->with('status', 'تم اعتماد الفاتورة وأصبحت للقراءة فقط.');
    }

    public function cancel(Request $request, Company $company, Invoice $invoice)
    {
        $this->authorizeCompany($company, $invoice);
        abort_unless($invoice->status === Invoice::STATUS_PENDING, 403, 'يمكن إلغاء الفواتير قيد الاعتماد فقط.');
        $before = $invoice->only('status');
        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);
        $this->audit->record('invoice.cancelled', $invoice, $before, $invoice->only('status'), $request);
        $this->notifications->record($invoice, 'cancelled', Auth::id());

        return back()->with('status', 'تم إلغاء الفاتورة.');
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
            'status' => $invoice->status ?: Invoice::STATUS_DRAFT,
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
            'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['nullable', Rule::exists('products', 'id')->where('company_id', $company->id)], 'items.*.description' => ['required', 'string'], 'items.*.quantity' => ['required', 'numeric', 'gt:0'], 'items.*.unit_price' => ['required', 'numeric', 'min:0'], 'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'], 'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
