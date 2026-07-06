<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

use function setPermissionsTeamId;

class UserManagementController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => User::with(['company', 'roles'])->latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.users.create', $this->formData(new User(['status' => 'active', 'role' => 'user'])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $roleIds = $data['roles'] ?? [];
        unset($data['roles'], $data['send_reset_link']);
        $data['password'] = Hash::make(str()->password(40));
        $user = User::create($data);
        setPermissionsTeamId((int) ($user->company_id ?: 0));
        $user->syncRoles($roleIds);
        if ($request->boolean('send_reset_link')) {
            Password::sendResetLink(['email' => $user->email]);
        }
        return redirect()->route('admin.users.show', $user)->with('success', 'تم إنشاء المستخدم.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', ['user' => $user->load(['company', 'roles.permissions'])]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', $this->formData($user->load('roles')));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $roleIds = $data['roles'] ?? [];
        unset($data['roles'], $data['send_reset_link']);
        if ($this->isLastSuperAdmin($user) && (($data['role'] ?? null) !== User::ROLE_SUPER_ADMIN || ($data['status'] ?? null) !== 'active')) {
            return back()->withErrors(['role' => 'لا يمكن تعديل أو تعطيل آخر Super Admin.'])->withInput();
        }
        $user->update($data);
        setPermissionsTeamId((int) ($user->company_id ?: 0));
        $user->syncRoles($roleIds);
        return redirect()->route('admin.users.show', $user)->with('success', 'تم تحديث المستخدم.');
    }

    public function activate(User $user): RedirectResponse
    {
        $user->forceFill(['status' => 'active'])->save();
        return back()->with('success', 'تم تفعيل المستخدم.');
    }

    public function suspend(User $user): RedirectResponse
    {
        if ($this->isLastSuperAdmin($user)) {
            return back()->withErrors(['status' => 'لا يمكن تعطيل آخر Super Admin.']);
        }
        $user->forceFill(['status' => 'suspended'])->save();
        return back()->with('success', 'تم تعطيل المستخدم.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($this->isLastSuperAdmin($user)) {
            return back()->withErrors(['user' => 'لا يمكن حذف آخر Super Admin.']);
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم.');
    }

    private function formData(User $user): array
    {
        return [
            'user' => $user,
            'companies' => Company::orderBy('name_ar')->get(),
            'roles' => Role::where('guard_name', 'web')->orderByRaw('company_id is not null')->orderBy('name')->get(),
            'assignedRoleIds' => $user->exists ? $user->roles->pluck('id')->all() : [],
        ];
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'role' => ['required', Rule::in([User::ROLE_SUPER_ADMIN, 'user'])],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'send_reset_link' => ['nullable', 'boolean'],
        ]);
    }

    private function isLastSuperAdmin(User $user): bool
    {
        return $user->role === User::ROLE_SUPER_ADMIN && User::where('role', User::ROLE_SUPER_ADMIN)->where('status', 'active')->whereKeyNot($user->id)->doesntExist();
    }
}
