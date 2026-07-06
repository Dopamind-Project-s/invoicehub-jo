<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
    private const PROTECTED_ROLES = ['Super Admin', 'Owner', 'Company Admin', 'Company Data Entry'];

    public function index()
    {
        return view('admin.roles.index', ['roles' => Role::withCount(['permissions', 'users'])->orderBy('company_id')->orderBy('name')->paginate(20)]);
    }

    public function create()
    {
        return view('admin.roles.create', $this->formData(new Role(['guard_name' => 'web'])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);
        $role = Role::create($data + ['guard_name' => 'web']);
        $role->syncPermissions($permissions);
        return redirect()->route('admin.roles.show', $role)->with('success', 'تم إنشاء الدور.');
    }

    public function show(Role $role)
    {
        return view('admin.roles.show', ['role' => $role->load('permissions')]);
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', $this->formData($role->load('permissions')));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->name === 'Super Admin') {
            return back()->withErrors(['role' => 'لا يمكن تعديل صلاحيات Super Admin من الواجهة.']);
        }
        $data = $this->validated($request, $role);
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);
        $role->update($data + ['guard_name' => 'web']);
        $role->syncPermissions($permissions);
        return redirect()->route('admin.roles.show', $role)->with('success', 'تم تحديث الدور.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->withErrors(['role' => 'لا يمكن حذف الأدوار الأساسية.']);
        }
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'تم حذف الدور.');
    }

    private function formData(Role $role): array
    {
        return [
            'role' => $role,
            'permissionGroups' => PermissionSeeder::GROUPS,
            'permissions' => Permission::where('guard_name', 'web')->orderBy('name')->get()->keyBy('name'),
            'assignedPermissions' => $role->exists ? $role->permissions->pluck('name')->all() : [],
            'protectedRoles' => self::PROTECTED_ROLES,
        ];
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('company_id', $request->input('company_id'))->ignore($role)],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);
    }
}
