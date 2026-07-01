<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TaxProfile;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxProfileController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request, Company $company)
    {
        $taxProfiles = TaxProfile::query()->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($s) => $s->where('name', 'like', '%'.$request->search.'%')->orWhere('tax_type', 'like', '%'.$request->search.'%')->orWhere('jofotara_tax_code', 'like', '%'.$request->search.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderByDesc('is_default')->orderBy('name')->paginate(15)->withQueryString();
        return view('company.master-data.tax-profiles.index', compact('company', 'taxProfiles'));
    }

    public function create(Company $company) { return view('company.master-data.tax-profiles.create', ['company' => $company, 'taxProfile' => new TaxProfile(['is_active' => true])]); }

    public function store(Request $request, Company $company)
    {
        $taxProfile = TaxProfile::create($this->validated($request) + ['company_id' => $company->id]);
        if ($taxProfile->is_default) TaxProfile::where('company_id', $company->id)->whereKeyNot($taxProfile->id)->update(['is_default' => false]);
        $this->audit->record('master_data.tax_profile.created', $taxProfile, [], $taxProfile->toArray(), $request);
        if ($request->input('save_action') === 'save_another') {
            return redirect()->route('company.tax-profiles.create', $company)->with('status', 'تم حفظ الضريبة، يمكنك إضافة ضريبة أخرى.');
        }

        return redirect()->route('company.tax-profiles.index', $company)->with('status', 'تم إنشاء ملف الضريبة.');
    }

    public function edit(Company $company, TaxProfile $taxProfile)
    {
        abort_unless((int) $taxProfile->company_id === (int) $company->id, 404);
        return view('company.master-data.tax-profiles.edit', compact('company', 'taxProfile'));
    }

    public function update(Request $request, Company $company, TaxProfile $taxProfile)
    {
        abort_unless((int) $taxProfile->company_id === (int) $company->id, 404);
        $before = $taxProfile->toArray(); $taxProfile->update($this->validated($request));
        if ($taxProfile->is_default) TaxProfile::where('company_id', $company->id)->whereKeyNot($taxProfile->id)->update(['is_default' => false]);
        $this->audit->record('master_data.tax_profile.updated', $taxProfile, $before, $taxProfile->toArray(), $request);
        return redirect()->route('company.tax-profiles.index', $company)->with('status', 'تم تحديث ملف الضريبة.');
    }

    public function activate(Request $request, Company $company, TaxProfile $taxProfile) { return $this->setActive($request, $company, $taxProfile, true); }
    public function deactivate(Request $request, Company $company, TaxProfile $taxProfile) { return $this->setActive($request, $company, $taxProfile, false); }
    private function setActive(Request $request, Company $company, TaxProfile $taxProfile, bool $active) { abort_unless((int) $taxProfile->company_id === (int) $company->id, 404); $before = $taxProfile->only('is_active'); $taxProfile->update(['is_active' => $active]); $this->audit->record('master_data.tax_profile.'.($active ? 'activated' : 'deactivated'), $taxProfile, $before, $taxProfile->only('is_active'), $request); return back(); }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255', 'not_regex:/^\s*$/u'], 'tax_type' => ['required', 'string', 'max:50', 'not_regex:/^\s*$/u'], 'tax_percent' => ['required', 'numeric', 'min:0', 'max:100'], 'jofotara_tax_code' => ['nullable', 'string', 'max:50'], 'is_default' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'], 'save_action' => ['nullable', Rule::in(['save', 'save_another'])]]) + ['is_default' => $request->boolean('is_default'), 'is_active' => $request->boolean('is_active')];
    }
}
