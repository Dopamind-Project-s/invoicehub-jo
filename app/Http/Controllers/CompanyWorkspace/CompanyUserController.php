<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class CompanyUserController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Company $company)
    {
        return view('company.users.index', ['company' => $company, 'users' => $company->users()->with('roles')->latest()->paginate(15)]);
    }

    public function create(Company $company)
    {
        return view('company.users.create', ['company' => $company, 'user' => new User(['status' => 'active']), 'roles' => $this->roles($company), 'assignedRoleIds' => []]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validated($request, $company);
        $roleIds = $data['roles'] ?? [];
        unset($data['roles']);
        $data['company_id'] = $company->id;
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->syncRoles($roleIds, $company->id);
        $this->audit->record('company.user.created', $user, [], $this->snapshot($user, $roleIds), $request);

        return redirect()->route('company.users.show', [$company, $user])->with('success', 'تم إنشاء المستخدم.');
    }

    public function show(Company $company, User $user)
    {
        $this->assertCompanyUser($company, $user);
        $user->load('roles');

        return view('company.users.show', compact('company', 'user'));
    }

    public function edit(Company $company, User $user)
    {
        $this->assertCompanyUser($company, $user);

        return view('company.users.edit', ['company' => $company, 'user' => $user->load('roles'), 'roles' => $this->roles($company), 'assignedRoleIds' => $user->roles()->wherePivot('company_id', $company->id)->pluck('roles.id')->all()]);
    }

    public function update(Request $request, Company $company, User $user): RedirectResponse
    {
        $this->assertCompanyUser($company, $user);
        $before = $this->snapshot($user, $user->roles()->wherePivot('company_id', $company->id)->pluck('roles.id')->all());
        $data = $this->validated($request, $company, $user);
        $roleIds = $data['roles'] ?? [];
        unset($data['roles'], $data['password']);
        $user->update($data);
        $user->syncRoles($roleIds, $company->id);
        $this->audit->record('company.user.updated', $user, $before, $this->snapshot($user->refresh(), $roleIds), $request);

        return redirect()->route('company.users.show', [$company, $user])->with('success', 'تم تحديث المستخدم.');
    }

    public function activate(Request $request, Company $company, User $user): RedirectResponse
    {
        return $this->status($request, $company, $user, 'active', 'company.user.activated', 'تم تفعيل المستخدم.');
    }

    public function suspend(Request $request, Company $company, User $user): RedirectResponse
    {
        return $this->status($request, $company, $user, 'suspended', 'company.user.suspended', 'تم تعليق المستخدم.');
    }

    public function resetPassword(Request $request, Company $company, User $user): RedirectResponse
    {
        $this->assertCompanyUser($company, $user);
        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $user->forceFill(['password' => Hash::make($data['password'])])->save();
        $this->audit->record('company.user.password_reset', $user, [], ['password' => '[changed]'], $request);

        return back()->with('success', 'تم تغيير كلمة المرور.');
    }

    private function validated(Request $request, Company $company, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')->where('company_id', $company->id)],
        ]);
    }

    private function status(Request $request, Company $company, User $user, string $status, string $action, string $message): RedirectResponse
    {
        $this->assertCompanyUser($company, $user);
        $before = $this->snapshot($user);
        $user->forceFill(['status' => $status])->save();
        $this->audit->record($action, $user, $before, $this->snapshot($user), $request);

        return back()->with('success', $message);
    }

    private function roles(Company $company)
    {
        return Role::where('company_id', $company->id)->orderBy('name')->get();
    }

    private function assertCompanyUser(Company $company, User $user): void
    {
        abort_unless((int) $user->company_id === (int) $company->id, 404);
    }

    private function snapshot(User $user, array $roleIds = []): array
    {
        return $user->only(['company_id', 'name', 'email', 'phone', 'status']) + ['role_ids' => $roleIds];
    }
}
