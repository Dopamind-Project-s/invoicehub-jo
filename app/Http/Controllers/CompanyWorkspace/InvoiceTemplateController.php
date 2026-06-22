<?php

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\InvoiceItem;
use App\Services\Invoices\InvoicePdfRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceTemplateController extends Controller
{
    public function index(Company $company)
    {
        $templates = InvoiceTemplate::query()->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))->where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $selected = CompanySetting::where('company_id', $company->id)->where('category', 'invoice_branding')->where('key', 'invoice_template_id')->value('value');
        return view('company.invoice-templates.index', compact('company', 'templates', 'selected'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate(['invoice_template_id' => ['required', Rule::exists('invoice_templates', 'id')]]);
        CompanySetting::updateOrCreate(['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_template_id'], ['value' => (string) $data['invoice_template_id']]);
        return back()->with('status', 'تم اختيار القالب الافتراضي للمنشأة.');
    }

    public function preview(Company $company, InvoiceTemplate $template, InvoicePdfRenderer $renderer)
    {
        abort_if($template->company_id !== null && (int) $template->company_id !== (int) $company->id, 404);
        $invoice = Invoice::query()->with(['company.settings', 'contact', 'items.product'])->where('company_id', $company->id)->latest()->first() ?: $this->sampleInvoice($company);
        return $renderer->stream($invoice, $template, 'template-'.$template->slug.'-preview.pdf');
    }

    private function sampleInvoice(Company $company): Invoice
    {
        $invoice = new Invoice(['company_id' => $company->id, 'supplier_id' => $company->id, 'invoice_number' => 'SAMPLE-001', 'status' => 'preview', 'issue_date' => now(), 'due_date' => now()->addDays(7), 'currency' => 'JOD', 'subtotal' => '100.000', 'discount_total' => '5.000', 'tax_total' => '15.200', 'grand_total' => '110.200', 'notes' => 'فاتورة تجريبية للمعاينة فقط.']);
        $invoice->setRelation('company', $company->loadMissing('settings'));
        $invoice->setRelation('contact', null);
        $invoice->setRelation('items', collect([new InvoiceItem(['description' => 'خدمة استشارية / Consulting service', 'quantity' => '1', 'unit_price' => '100.000', 'discount_amount' => '5.000', 'tax_amount' => '15.200', 'line_total' => '110.200'])]));
        return $invoice;
    }
}
