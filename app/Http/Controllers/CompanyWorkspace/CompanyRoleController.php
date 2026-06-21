<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function setPermissionsTeamId;

class CompanyRoleController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Company $company)
    {
        return view('company.roles.index', ['company' => $company, 'roles' => Role::with('permissions')->where('company_id', $company->id)->orderBy('name')->get(), 'permissions' => Permission::orderBy('name')->get()]);
    }

    public function update(Request $request, Company $company, Role $role): RedirectResponse
    {
        abort_unless((int) $role->company_id === (int) $company->id, 404);
        setPermissionsTeamId($company->id);
        $data = $request->validate(['permissions' => ['array'], 'permissions.*' => ['string', 'exists:permissions,name']]);
        $before = ['permissions' => $role->permissions()->pluck('name')->all()];
        $role->syncPermissions($data['permissions'] ?? []);
        $this->audit->record('company.role.permissions_updated', $role, $before, ['permissions' => $data['permissions'] ?? []], $request);

        return back()->with('success', 'تم تحديث صلاحيات الدور.');
    }
}
