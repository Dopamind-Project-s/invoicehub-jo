<?php

namespace App\Services\Invoices;

use App\Models\Company;
use App\Models\InvoiceTemplate;

class InvoiceBrandingService
{
    public function settings(Company $company): array
    {
        $settings = $company->settings()->where('category', 'invoice_branding')->pluck('value', 'key');
        $template = InvoiceTemplate::query()->find($settings->get('invoice_template_id'))
            ?: InvoiceTemplate::query()->whereNull('company_id')->where('is_default', true)->first();

        return [
            'template' => $template,
            'logo' => $settings->get('invoice_logo') ?: $company->logo_path,
            'primary_color' => $settings->get('invoice_primary_color') ?: '#00a9c4',
            'secondary_color' => $settings->get('invoice_secondary_color') ?: '#12c2b2',
            'footer_text' => $settings->get('invoice_footer_text') ?: 'شكراً لتعاملكم معنا.',
            'terms_and_conditions' => $settings->get('invoice_terms_and_conditions') ?: null,
            'signature_block' => $settings->get('invoice_signature_block') ?: null,
            'stamp_image' => $settings->get('invoice_stamp_image') ?: null,
        ];
    }
}
