<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Unit;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request, Company $company)
    {
        $units = Unit::query()->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($s) => $s->where('code', 'like', '%'.$request->search.'%')->orWhere('name_ar', 'like', '%'.$request->search.'%')->orWhere('name_en', 'like', '%'.$request->search.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderByRaw('company_id is not null desc')->orderBy('code')->paginate(15)->withQueryString();
        return view('company.master-data.units.index', compact('company', 'units'));
    }

    public function create(Company $company) { return view('company.master-data.units.create', ['company' => $company, 'unit' => new Unit(['is_active' => true])]); }

    public function store(Request $request, Company $company)
    {
        $unit = Unit::create($this->validated($request, $company) + ['company_id' => $company->id, 'name' => $request->input('name_ar')]);
        $this->audit->record('master_data.unit.created', $unit, [], $unit->toArray(), $request);
        if ($request->input('save_action') === 'save_another') {
            return redirect()->route('company.units.create', $company)->with('status', 'تم حفظ الوحدة، يمكنك إضافة وحدة أخرى.');
        }

        return redirect()->route('company.units.index', $company)->with('status', 'تم إنشاء الوحدة.');
    }

    public function edit(Company $company, Unit $unit)
    {
        abort_unless($unit->company_id === null || (int) $unit->company_id === (int) $company->id, 404);
        return view('company.master-data.units.edit', compact('company', 'unit'));
    }

    public function update(Request $request, Company $company, Unit $unit)
    {
        abort_unless($unit->company_id === null || (int) $unit->company_id === (int) $company->id, 404);
        $before = $unit->toArray();
        $unit->update($this->validated($request, $company, $unit) + ['name' => $request->input('name_ar')]);
        $this->audit->record('master_data.unit.updated', $unit, $before, $unit->toArray(), $request);
        return redirect()->route('company.units.index', $company)->with('status', 'تم تحديث الوحدة.');
    }

    public function activate(Request $request, Company $company, Unit $unit) { return $this->setActive($request, $company, $unit, true); }
    public function deactivate(Request $request, Company $company, Unit $unit) { return $this->setActive($request, $company, $unit, false); }

    private function setActive(Request $request, Company $company, Unit $unit, bool $active)
    {
        abort_unless($unit->company_id === null || (int) $unit->company_id === (int) $company->id, 404);
        $before = $unit->only('is_active'); $unit->update(['is_active' => $active]);
        $this->audit->record('master_data.unit.'.($active ? 'activated' : 'deactivated'), $unit, $before, $unit->only('is_active'), $request);
        return back();
    }

    private function validated(Request $request, Company $company, ?Unit $unit = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'not_regex:/^\s*$/u', Rule::unique('units', 'code')->where('company_id', $company->id)->ignore($unit)],
            'name_ar' => ['required', 'string', 'max:255', 'not_regex:/^\s*$/u'], 'name_en' => ['nullable', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:50'], 'description' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean'],
            'save_action' => ['nullable', Rule::in(['save', 'save_another'])],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
