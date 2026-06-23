<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceTemplateSeeder extends Seeder
{
    private const TEMPLATES = [
        ['Arabic Classic', 'arabic-classic', 'ar', 'classic', 'company.invoice-templates.render.arabic-classic', true],
        ['Arabic Modern', 'arabic-modern', 'ar', 'modern', 'company.invoice-templates.render.arabic-modern', false],
        ['Arabic / English Bilingual', 'bilingual-ar-en', 'ar_en', 'bilingual', 'company.invoice-templates.render.bilingual-ar-en', false],
        ['Retail Receipt', 'retail-receipt', 'ar', 'receipt', 'company.invoice-templates.render.retail-receipt', false],
        ['Corporate Tax Invoice', 'corporate-tax', 'ar_en', 'corporate', 'company.invoice-templates.render.corporate-tax', false],
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as [$name, $slug, $language, $layoutType, $viewPath, $isDefault]) {
            DB::table('invoice_templates')->updateOrInsert(
                ['company_id' => null, 'slug' => $slug],
                ['name' => $name, 'language' => $language, 'layout_type' => $layoutType, 'preview_image' => 'assets/templates/'.$slug.'.png', 'view_path' => $viewPath, 'is_default' => $isDefault, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $defaultTemplateId = DB::table('invoice_templates')->whereNull('company_id')->where('slug', 'arabic-classic')->value('id');
        DB::table('companies')->select('id')->orderBy('id')->chunkById(100, function ($companies) use ($defaultTemplateId): void {
            foreach ($companies as $company) {
                foreach (['invoice_template_id' => (string) $defaultTemplateId, 'invoice_primary_color' => '#00a9c4', 'invoice_secondary_color' => '#12c2b2', 'invoice_footer_text' => 'شكراً لتعاملكم معنا.', 'invoice_terms_and_conditions' => 'تخضع هذه الفاتورة لشروط وأحكام الشركة.', 'invoice_signature_block' => 'المفوض بالتوقيع', 'invoice_logo' => '', 'invoice_stamp_image' => ''] as $key => $value) {
                    DB::table('company_settings')->updateOrInsert(
                        ['company_id' => $company->id, 'key' => $key],
                        ['category' => 'invoice_branding', 'value' => $value, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        });
    }
}
