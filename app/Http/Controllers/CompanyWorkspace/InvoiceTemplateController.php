<?php

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\InvoiceTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceTemplateController extends Controller
{
    public function index(Company $company)
    {
        $templates = InvoiceTemplate::query()->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))->where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $selected = CompanySetting::where('company_id', $company->id)->where('key', 'invoice_template_id')->value('value');
        return view('company.invoice-templates.index', compact('company', 'templates', 'selected'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate(['invoice_template_id' => ['required', Rule::exists('invoice_templates', 'id')]]);
        CompanySetting::updateOrCreate(['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_template_id'], ['value' => (string) $data['invoice_template_id']]);
        return back()->with('status', 'تم تحديث قالب فواتير المنشأة.');
    }
}
