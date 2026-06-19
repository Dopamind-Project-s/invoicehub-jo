<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Console\Commands\CreateRealJofotaraSample;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Jofotara\JoFotaraApiService;
use App\Services\Jofotara\JoFotaraPreparationService;
use App\Services\Jofotara\QRCodeService;
use App\Services\Jofotara\TaxCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('invoices.index', ['invoices' => Invoice::with(['supplier', 'customer'])->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('invoices.form', $this->formData(new Invoice([
            'supplier_id' => Company::where('is_active', true)->value('id'),
            'issue_date' => now(),
            'issue_time' => now()->format('H:i:s'),
            'invoice_scope' => 'local',
            'payment_type' => 'receivable',
            'taxpayer_type' => 'income',
            'currency_code' => 'JOD',
        ])));
    }

    public function store(Request $request, TaxCalculationService $tax): RedirectResponse
    {
        $invoice = $this->saveInvoice(new Invoice, $this->validatedInvoice($request), $tax);

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم حفظ مسودة الفاتورة.');
    }

    public function show(Invoice $invoice)
    {
        return view('invoices.show', ['invoice' => $invoice->load(['supplier', 'customer', 'items'])]);
    }

    public function edit(Invoice $invoice)
    {
        abort_if($invoice->status === 'ACCEPTED', 403, 'لا يمكن تعديل فاتورة مقبولة.');

        return view('invoices.form', $this->formData($invoice->load('items')));
    }

    public function update(Request $request, Invoice $invoice, TaxCalculationService $tax): RedirectResponse
    {
        abort_if($invoice->status === 'ACCEPTED', 403, 'لا يمكن تعديل فاتورة مقبولة.');
        $invoice = $this->saveInvoice($invoice, $this->validatedInvoice($request, $invoice), $tax);

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم تحديث مسودة الفاتورة.');
    }

    public function createRealSample(): RedirectResponse
    {
        Artisan::call(CreateRealJofotaraSample::class);

        return redirect()->route('invoices.index')->with('success', trim(Artisan::output()));
    }

    public function prepare(Invoice $invoice, JoFotaraPreparationService $preparer): RedirectResponse
    {
        try {
            $prepared = $preparer->prepare($invoice);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'تم تجهيز XML وحفظه: '.$prepared['xml_path']);
    }

    public function submitReal(Invoice $invoice, JoFotaraApiService $api): RedirectResponse
    {
        try {
            $result = $api->submit($invoice);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with($result['status'] === 'ACCEPTED' ? 'success' : 'error', $result['status'] === 'ACCEPTED' ? 'تم قبول الفاتورة من جوفوتارا.' : 'لم يتم قبول الفاتورة. راجع الرد المحفوظ.');
    }

    public function updateQr(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate(['qr_code' => ['nullable', 'string']]);
        $invoice->forceFill(['qr_code' => $data['qr_code'] ?: null])->save();

        return back()->with('success', 'تم تحديث قيمة QR.');
    }

    public function qr(Invoice $invoice, QRCodeService $qr)
    {
        $png = $qr->png($invoice);
        abort_if($png === null, 404, 'QR is available only for accepted invoices with QR value.');

        return response($png, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'no-store']);
    }

    public function downloadXml(Invoice $invoice): BinaryFileResponse
    {
        return response()->download(storage_path("app/jofotara/invoice-{$invoice->id}/invoice.xml"));
    }

    public function downloadPayload(Invoice $invoice): BinaryFileResponse
    {
        return response()->download(storage_path("app/jofotara/invoice-{$invoice->id}/payload.json"));
    }

    public function issuedPdf(Invoice $invoice, QRCodeService $qr)
    {
        $invoice->load(['supplier', 'customer', 'items']);
        $qrDataUri = $qr->dataUri($invoice);
        if (class_exists(Pdf::class)) {
            return Pdf::loadView('invoices.issued-pdf', compact('invoice', 'qrDataUri'))->setPaper('a4', 'portrait')->download($invoice->invoice_number.'-issued.pdf');
        }

        return view('invoices.issued-pdf', compact('invoice', 'qrDataUri'));
    }

    private function formData(Invoice $invoice): array
    {
        return ['invoice' => $invoice, 'companies' => Company::where('is_active', true)->orderBy('legal_name_ar')->get(), 'customers' => Customer::orderBy('name')->get()];
    }

    private function validatedInvoice(Request $request, ?Invoice $invoice = null): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'exists:companies,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'issue_date' => ['required', 'date'],
            'issue_time' => ['required'],
            'invoice_scope' => ['required', 'in:local,export,development_area'],
            'taxpayer_type' => ['required', 'in:income,general_sales,special_sales'],
            'payment_type' => ['required', 'in:cash,receivable'],
            'currency_code' => ['required', 'size:3'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function saveInvoice(Invoice $invoice, array $data, TaxCalculationService $tax): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $tax): Invoice {
            $company = Company::findOrFail($data['supplier_id']);
            abort_if(blank($company->tax_number) || blank($company->jofotara_source_id), 422, 'الشركة تحتاج الرقم الضريبي وتسلسل مصدر الدخل.');
            $customer = filled($data['customer_id'] ?? null) ? Customer::find($data['customer_id']) : Customer::firstOrCreate(['name' => 'عميل نقدي'], ['customer_type' => 'INDIVIDUAL', 'country_code' => 'JO', 'phone' => '-']);
            abort_if($customer && (preg_match('/^0+$/', (string) $customer->tax_number) || preg_match('/^0+$/', (string) $customer->national_number)), 422, 'لا يسمح برقم مشتري وهمي.');
            $items = collect($data['items'])->map(fn (array $item): array => [
                'description' => $item['description'],
                'quantity' => (string) $item['quantity'],
                'unit_price' => (string) $item['unit_price'],
                'discount' => (string) ($item['discount'] ?? 0),
                'tax_category' => ((float) ($item['tax_percent'] ?? 0)) > 0 ? 'S' : 'Z',
                'tax_percent' => (string) ($item['tax_percent'] ?? 0),
            ])->values();
            $totals = $tax->calculateInvoice($items->all());
            $invoice->forceFill(array_merge($totals, [
                'uuid' => $invoice->uuid ?: (string) Str::uuid(),
                'invoice_number' => $invoice->invoice_number ?: 'INV_'.now()->year.'_'.str_pad((string) (Invoice::whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT),
                'icv' => $invoice->icv ?: (int) $company->last_icv + 1,
                'invoice_type' => 'STANDARD',
                'invoice_subtype' => 'SALE',
                'invoice_scope' => $data['invoice_scope'],
                'payment_type' => $data['payment_type'],
                'taxpayer_type' => $data['taxpayer_type'],
                'issue_date' => $data['issue_date'],
                'issue_time' => $data['issue_time'],
                'currency_code' => $data['currency_code'],
                'exchange_rate' => '1.000000',
                'supplier_id' => $company->id,
                'customer_id' => $customer?->id,
                'status' => $invoice->status ?: 'DRAFT',
            ]))->save();
            if ((int) $company->last_icv < (int) $invoice->icv) {
                $company->forceFill(['last_icv' => $invoice->icv])->save();
            }
            $invoice->items()->delete();
            foreach ($items as $item) {
                $line = $tax->calculateLine($item['quantity'], $item['unit_price'], $item['discount'], $item['tax_percent']);
                $invoice->items()->create(array_merge($item, ['line_extension_amount' => $line['line_extension_amount'], 'tax_amount' => $line['tax_amount'], 'line_total' => $line['line_total']]));
            }

            return $invoice;
        });
    }
}
