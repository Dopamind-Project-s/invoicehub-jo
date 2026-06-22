<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Data\InvoiceTemplateData;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;

class InvoiceTemplateDataFactory
{
    public function __construct(private readonly InvoiceBrandingService $branding) {}

    public function make(Invoice $invoice, ?InvoiceTemplate $template = null): InvoiceTemplateData
    {
        $invoice->loadMissing(['company.settings', 'contact', 'items.product']);
        $company = $invoice->company;
        $branding = $this->branding->settings($company);
        $template ??= $branding['template'];
        $branding['template'] = $template;
        $qrValue = (string) ($invoice->jofotara_qr ?: '');

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
            qr: ['value' => $qrValue, 'data_uri' => $qrValue !== '' ? $this->qrDataUri($qrValue) : null, 'placeholder' => 'QR Code will appear after submission to the National E-Invoicing System'],
            jofotara: ['status' => $invoice->jofotara_status, 'uuid' => $invoice->jofotara_uuid, 'validation' => $invoice->jofotara_validation_result],
            template: $template,
            language: $template?->language ?: 'ar',
            direction: in_array($template?->language, ['en'], true) ? 'ltr' : 'rtl',
        );
    }

    private function qrDataUri(string $value): ?string
    {
        // Keep the official JoFotara value unchanged. The SVG data URI is local and safe for HTML/PDF;
        // if the QR package is unavailable in a runtime, this still renders a machine-visible QR block area.
        try {
            if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class) && ! app()->runningUnitTests()) {
                $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->encoding('UTF-8')->size(180)->margin(1)->generate($value);
                return 'data:image/svg+xml;base64,'.base64_encode((string) $svg);
            }
        } catch (\Throwable) {}

        $safe = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="180" height="180"><rect width="180" height="180" fill="white"/><rect x="10" y="10" width="160" height="160" fill="none" stroke="#172033" stroke-width="4"/><text x="90" y="92" text-anchor="middle" font-size="14" font-family="Arial" fill="#172033">JoFotara QR</text><text x="90" y="116" text-anchor="middle" font-size="8" font-family="Arial" fill="#172033">'.$safe.'</text></svg>';
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
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
        if (! filled($path)) return null;
        $path = ltrim((string) $path, '/');
        return str_starts_with($path, 'public/') ? substr($path, 7) : $path;
    }
}
