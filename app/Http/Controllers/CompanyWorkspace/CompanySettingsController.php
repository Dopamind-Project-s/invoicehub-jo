<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Services\Audit\AuditLogger;
use App\Services\CompanyWorkspace\CompanyDashboardStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function edit(Company $company)
    {
        $settings = $company->settings->keyBy('key');

        return view('company.settings.edit', ['company' => $company, 'settings' => $settings, 'categories' => $this->definitions()]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'settings' => ['array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
            'company_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'invoice_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'invoice_stamp_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        foreach (['company_logo_file' => 'company_logo', 'invoice_logo_file' => 'invoice_logo', 'invoice_stamp_image_file' => 'invoice_stamp_image'] as $input => $key) {
            if ($request->hasFile($input)) {
                $data['settings'][$key] = $request->file($input)->store('invoice-branding', 'public');
            }
        }

        $before = $company->settings()->pluck('value', 'key')->all();
        foreach ($this->definitions() as $category => $keys) {
            foreach ($keys as $key => $label) {
                $value = $data['settings'][$key] ?? ($before[$key] ?? null);
                CompanySetting::updateOrCreate(['company_id' => $company->id, 'key' => $key], ['category' => $category, 'value' => $value]);
            }
        }
        $after = $company->settings()->pluck('value', 'key')->all();
        $this->audit->record('company.settings.updated', $company, $before, $after, $request);
        CompanyDashboardStatsService::forget($company);

        return back()->with('success', 'تم حفظ إعدادات المنشأة.');
    }

    private function definitions(): array
    {
        return [
            'بيانات عامة' => ['company_logo' => 'شعار المنشأة'],
            'الهوية البصرية' => ['brand_color' => 'لون الهوية', 'invoice_logo' => 'شعار الفاتورة', 'invoice_primary_color' => 'اللون الأساسي', 'invoice_secondary_color' => 'اللون الثانوي', 'invoice_stamp_image' => 'صورة الختم'],
            'إعدادات الفواتير' => ['invoice_template_id' => 'قالب الفاتورة الافتراضي', 'invoice_prefix' => 'بادئة الفاتورة', 'invoice_footer_text' => 'نص التذييل', 'invoice_terms_and_conditions' => 'الشروط والأحكام', 'invoice_signature_block' => 'كتلة التوقيع'],
            'إعدادات جوفوتارا' => ['jofotara_mode' => 'وضع جوفوتارا'],
            'اللغة والعملة' => ['default_language' => 'اللغة الافتراضية', 'default_currency' => 'العملة الافتراضية'],
        ];
    }
}
