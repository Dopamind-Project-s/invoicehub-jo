<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Data\InvoiceTemplateData;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Services\Jofotara\QRCodeService;

class InvoiceTemplateDataFactory
{
    public function __construct(private readonly InvoiceBrandingService $branding, private readonly QRCodeService $qr, private readonly InvoiceDisplayDataFactory $displayData) {}

    public function make(Invoice $invoice, ?InvoiceTemplate $template = null): InvoiceTemplateData
    {
        $invoice->loadMissing(['company.settings', 'contact', 'items.product']);
        $company = $invoice->company;
        $branding = $this->branding->settings($company);
        $template ??= $branding['template'] ?? InvoiceTemplate::query()->whereNull('company_id')->where('slug', 'arabic-classic')->first();
        $template ??= new InvoiceTemplate(['name' => 'Arabic Classic', 'slug' => 'arabic-classic', 'language' => 'ar', 'layout_type' => 'classic', 'view_path' => 'company.invoice-templates.render.arabic-classic']);
        $branding['template'] = $template;
        $qrValue = $this->qr->officialValue($invoice);

        return new InvoiceTemplateData(
            invoice: $invoice,
            company: $company,
            customer: $invoice->contact,
            items: $invoice->items,
            totals: [
                'subtotal' => $invoice->subtotal ?: $invoice->total_amount,
                'discount' => $invoice->discount_total ?: $invoice->discount_amount,
                'tax' => $invoice->tax_total ?: $invoice->tax_amount,
                'grand' => $invoice->grand_total ?: $invoice->payable_amount,
                'currency' => $invoice->currency ?: $invoice->currency_code ?: $company->default_currency ?: 'JOD',
            ],
            branding: $this->normalizeBranding($branding, $company),
            qr: ['value' => $qrValue, 'data_uri' => $this->qr->dataUri($invoice), 'placeholder' => 'رمز QR الرسمي غير متوفر لأن الفاتورة لم تُعتمد بعد من نظام الفوترة الوطني'],
            jofotara: ['status' => $invoice->jofotara_status, 'uuid' => $invoice->jofotara_uuid, 'validation' => $invoice->jofotara_validation_result],
            template: $template,
            language: $template?->language ?: 'ar',
            direction: in_array($template?->language, ['en'], true) ? 'ltr' : 'rtl',
            doc: $this->displayData->make($invoice),
        );
    }

    private function normalizeBranding(array $branding, Company $company): array
    {
        $branding['logo'] = $this->publicPath($branding['logo'] ?? null);
        $branding['stamp_image'] = $this->publicPath($branding['stamp_image'] ?? null);
        $branding['initials'] = mb_substr($company->name_ar ?: $company->legal_name_ar ?: $company->name_en ?: 'IH', 0, 2);

        return $branding;
    }

    private function publicPath(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }
        $path = ltrim((string) $path, '/');

        return str_starts_with($path, 'public/') ? substr($path, 7) : $path;
    }
}
