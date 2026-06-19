<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Services\Audit\AuditLogger;
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
        $data = $request->validate(['settings' => ['array'], 'settings.*' => ['nullable', 'string', 'max:2000']]);
        $before = $company->settings()->pluck('value', 'key')->all();
        foreach ($this->definitions() as $category => $keys) {
            foreach ($keys as $key => $label) {
                CompanySetting::updateOrCreate(['company_id' => $company->id, 'key' => $key], ['category' => $category, 'value' => $data['settings'][$key] ?? null]);
            }
        }
        $after = $company->settings()->pluck('value', 'key')->all();
        $this->audit->record('company.settings.updated', $company, $before, $after, $request);

        return back()->with('success', 'تم حفظ الإعدادات.');
    }

    private function definitions(): array
    {
        return [
            'General' => ['company_logo' => 'Company logo'],
            'Branding' => ['brand_color' => 'Brand color'],
            'Localization' => ['default_language' => 'Default language', 'default_currency' => 'Default currency'],
            'Invoice Defaults' => ['invoice_template' => 'Invoice template', 'invoice_prefix' => 'Invoice prefix'],
            'JoFotara Settings' => ['jofotara_mode' => 'JoFotara mode'],
        ];
    }
}
