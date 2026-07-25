<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\InvoiceTemplate;

class InvoiceTemplateResolver
{
    /** @return array<string, string> */
    public function presentation(InvoiceTemplate|string|null $template): array
    {
        $slug = $template instanceof InvoiceTemplate ? $template->slug : $template;

        return self::definitions()[$slug] ?? self::definitions()['arabic-classic'];
    }

    /** @return array<string, array<string, string>> */
    public static function definitions(): array
    {
        return [
            'arabic-classic' => self::definition('arabic-classic', 'classic-formal', 'horizontal-formal', 'bordered-pairs', 'tax-grid', 'boxed-summary', 'compliance-footer', 'متوسط', 'فواتير ضريبية عربية رسمية'),
            'arabic-modern' => self::definition('arabic-modern', 'modern-asymmetric', 'accent-panel', 'offset-panels', 'soft-rows', 'floating-panel', 'verification-strip', 'واسع', 'تصميم عربي حديث يبرز هوية المنشأة'),
            'bilingual-ar-en' => self::definition('bilingual-ar-en', 'bilingual-balanced', 'dual-language', 'mirrored-bilingual', 'dual-labels', 'balanced-summary', 'bilingual-compliance', 'متوسط', 'تصميم ثنائي اللغة للمعاملات العربية والإنجليزية'),
            'corporate-tax' => self::definition('corporate-tax', 'corporate-branded', 'brand-band', 'corporate-cards', 'striped-enterprise', 'brand-sidebar', 'corporate-verification', 'واسع', 'تصميم مؤسسي حديث للفواتير الضريبية'),
            'jordan-tax-pro' => self::definition('jordan-tax-pro', 'regulatory-jordan', 'regulatory-masthead', 'seller-buyer', 'bordered-regulatory', 'tax-payable-split', 'regulatory-panel', 'متوسط', 'تصميم رسمي متوافق مع الطابع الضريبي الأردني'),
            'minimal-blue' => self::definition('minimal-blue', 'minimal-service', 'compact-line', 'inline-details', 'description-first', 'open-totals', 'quiet-corner', 'مريح', 'تصميم بسيط للخدمات والاستشارات'),
            'premium-ledger' => self::definition('premium-ledger', 'premium-ledger', 'ledger-heading', 'divided-ledger', 'ledger-rows', 'signed-summary', 'regulatory-seal', 'واسع', 'تصميم دفتر مالي أنيق للمكاتب المهنية'),
            'retail-receipt' => self::definition('retail-receipt', 'retail-compact', 'compact-receipt', 'dense-inline', 'dense-products', 'immediate-total', 'total-adjacent', 'مدمج', 'تصميم مدمج للمتاجر والفواتير متعددة البنود'),
        ];
    }

    /** @return array<string, string> */
    private static function definition(string $slug, string $layout, string $header, string $info, string $table, string $totals, string $qr, string $density, string $description): array
    {
        return compact('slug', 'layout', 'header', 'info', 'table', 'totals', 'qr', 'density', 'description') + [
            'root_class' => 'invoice-template-'.$slug,
            'stylesheet' => 'css/invoice-templates/'.$slug.'.css',
        ];
    }
}
