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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanySettingsController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function edit(Company $company)
    {
        $settings = $company->settings->keyBy('key');
        $currencies = DB::table('currencies')
            ->select('code', 'name')
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN code = 'JOD' THEN 0 ELSE 1 END")
            ->orderBy('code')
            ->get();

        return view('company.settings.edit', ['company' => $company, 'settings' => $settings, 'categories' => $this->definitions(), 'currencies' => $currencies]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $currencyCodes = DB::table('currencies')->where('is_active', true)->pluck('code')->all();

        $data = $request->validate([
            'settings' => ['array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
            'settings.default_language' => ['nullable', Rule::in(['ar', 'en'])],
            'settings.default_currency' => ['nullable', 'string', Rule::in($currencyCodes ?: ['JOD', 'USD', 'EUR'])],
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
        $company->forceFill([
            'default_language' => $data['settings']['default_language'] ?? $company->default_language,
            'default_currency' => $data['settings']['default_currency'] ?? $company->default_currency,
        ])->save();

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
