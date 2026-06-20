<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceTemplateSeeder extends Seeder
{
    private const TEMPLATES = [
        ['Arabic Classic', 'arabic-classic', 'ar', 'classic', true],
        ['Arabic Corporate', 'arabic-corporate', 'ar', 'corporate', false],
        ['Arabic + English', 'arabic-english', 'ar_en', 'bilingual', false],
        ['Retail Receipt', 'retail-receipt', 'ar', 'receipt', false],
        ['Professional Tax Invoice', 'professional-tax-invoice', 'en', 'tax', false],
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as [$name, $slug, $language, $layoutType, $isDefault]) {
            DB::table('invoice_templates')->updateOrInsert(
                ['company_id' => null, 'slug' => $slug],
                ['name' => $name, 'language' => $language, 'layout_type' => $layoutType, 'is_default' => $isDefault, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $defaultTemplateId = DB::table('invoice_templates')->whereNull('company_id')->where('slug', 'arabic-classic')->value('id');
        DB::table('companies')->select('id')->orderBy('id')->chunkById(100, function ($companies) use ($defaultTemplateId): void {
            foreach ($companies as $company) {
                foreach ([
                    'invoice_template_id' => (string) $defaultTemplateId,
                    'invoice_primary_color' => '#00a9c4',
                    'invoice_secondary_color' => '#12c2b2',
                    'invoice_footer_text' => 'شكراً لتعاملكم معنا.',
                    'invoice_terms_and_conditions' => 'تخضع هذه الفاتورة لشروط وأحكام الشركة.',
                    'invoice_signature_block' => 'المفوض بالتوقيع',
                    'invoice_logo' => '',
                    'invoice_stamp_image' => '',
                ] as $key => $value) {
                    DB::table('company_settings')->updateOrInsert(
                        ['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => $key],
                        ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        });
    }
}
