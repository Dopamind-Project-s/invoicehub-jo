<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FeatureKey;
use App\Services\Audit\AuditLogger;
use App\Services\Company\CompanyRoleSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CompanyManagementController extends Controller
{
    public function __construct(private readonly AuditLogger $audit, private readonly CompanyRoleSeeder $roles) {}

    public function index()
    {
        return view('admin.companies.index', [
            'companies' => Company::withCount('featureKeys')->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.companies.create', [
            'company' => new Company(['status' => 'active', 'is_active' => true, 'default_language' => 'ar', 'default_currency' => 'JOD', 'country_code' => 'JO', 'icv_prefix' => 'INV']),
            'features' => FeatureKey::where('is_active', true)->orderBy('code')->get(),
            'enabledFeatureIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $features = $data['feature_keys'] ?? [];
        unset($data['feature_keys']);

        $company = Company::create($this->payload($data, $request));
        $company->featureKeys()->sync($features);
        $this->roles->seed($company);
        $this->audit->record('admin.company.created', $company, [], $this->auditSnapshot($company), $request);
        $this->audit->record('admin.company.features.synced', $company, [], ['feature_key_ids' => $features], $request);

        return redirect()->route('admin.companies.show', $company)->with('success', 'تم إنشاء الشركة.');
    }

    public function show(Company $company)
    {
        $company->load(['featureKeys', 'activeSubscription.plan']);

        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', [
            'company' => $company->load('featureKeys'),
            'features' => FeatureKey::where('is_active', true)->orderBy('code')->get(),
            'enabledFeatureIds' => $company->featureKeys->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $before = $this->auditSnapshot($company->fresh());
        $data = $this->validated($request, $company);
        $features = $data['feature_keys'] ?? [];
        unset($data['feature_keys']);

        $company->update($this->payload($data, $request, $company));
        $oldFeatures = $company->featureKeys()->pluck('feature_keys.id')->all();
        $company->featureKeys()->sync($features);
        $company->refresh();

        $this->audit->record('admin.company.updated', $company, $before, $this->auditSnapshot($company), $request);
        if ($oldFeatures !== array_map('intval', $features)) {
            $this->audit->record('admin.company.features.synced', $company, ['feature_key_ids' => $oldFeatures], ['feature_key_ids' => $features], $request);
        }

        return redirect()->route('admin.companies.show', $company)->with('success', 'تم تحديث الشركة.');
    }

    public function activate(Request $request, Company $company): RedirectResponse
    {
        $before = $this->auditSnapshot($company);
        $company->forceFill(['status' => 'active', 'is_active' => true])->save();
        $this->audit->record('admin.company.activated', $company, $before, $this->auditSnapshot($company), $request);

        return back()->with('success', 'تم تفعيل الشركة.');
    }

    public function suspend(Request $request, Company $company): RedirectResponse
    {
        $before = $this->auditSnapshot($company);
        $company->forceFill(['status' => 'suspended', 'is_active' => false])->save();
        $this->audit->record('admin.company.suspended', $company, $before, $this->auditSnapshot($company), $request);

        return back()->with('success', 'تم تعليق الشركة.');
    }

    private function validated(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['required', 'string', 'max:50', Rule::unique('companies', 'tax_number')->ignore($company)],
            'national_number' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'logo' => ['nullable', 'image', 'max:2048'],
            'jofotara_source_id' => ['nullable', 'string', 'max:50'],
            'jofotara_client_id' => ['nullable', 'string'],
            'jofotara_secret_key' => ['nullable', 'string'],
            'default_language' => ['required', Rule::in(['ar', 'en'])],
            'default_currency' => ['required', 'string', 'size:3'],
            'feature_keys' => ['array'],
            'feature_keys.*' => ['integer', 'exists:feature_keys,id'],
        ]);
    }

    private function payload(array $data, Request $request, ?Company $company = null): array
    {
        $data['legal_name_ar'] = $data['name_ar'];
        $data['legal_name_en'] = $data['name_en'] ?? null;
        $data['is_active'] = $data['status'] === 'active';
        $data['country_code'] = $company?->country_code ?: 'JO';
        $data['icv_prefix'] = $company?->icv_prefix ?: 'INV';

        foreach (['jofotara_client_id', 'jofotara_secret_key'] as $credential) {
            if (blank($data[$credential] ?? null)) {
                unset($data[$credential]);
            }
            if ($company && ! array_key_exists($credential, $data)) {
                $data[$credential] = $company->{$credential};
            }
        }

        if ($request->hasFile('logo')) {
            if ($company?->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('company-logos', 'public');
        }

        return $data;
    }

    private function auditSnapshot(Company $company): array
    {
        return $company->only([
            'name_ar', 'name_en', 'legal_name_ar', 'legal_name_en', 'tax_number', 'national_number', 'phone', 'email', 'status', 'logo_path', 'jofotara_source_id', 'jofotara_client_id', 'jofotara_secret_key', 'default_language', 'default_currency', 'is_active',
        ]);
    }
}
