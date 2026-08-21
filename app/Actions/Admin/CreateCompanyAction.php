<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Company;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Company\CompanyRoleSeeder;
use App\Services\Subscriptions\AdminSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CreateCompanyAction
{
    public function __construct(private readonly CompanyRoleSeeder $roles, private readonly AdminSubscriptionService $subscriptions, private readonly AuditLogger $audit) {}

    public function execute(array $companyData, array $options, Request $request): Company
    {
        return DB::transaction(function () use ($companyData, $options, $request): Company {
            $normalizedEmail = mb_strtolower(trim((string) $companyData['email']));
            if (User::where('email', $normalizedEmail)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['email' => 'البريد الإلكتروني مستخدم لحساب آخر بالفعل.']);
            }
            $company = Company::create($companyData);
            $this->roles->seed($company);
            $previousTeamId = getPermissionsTeamId();
            try {
                setPermissionsTeamId($company->id);
                $user = User::create([
                    'company_id' => $company->id,
                    'name' => $company->name_ar ?: $company->legal_name_ar ?: $company->name_en ?: "Company #{$company->id}",
                    'email' => $normalizedEmail,
                    'password' => Hash::make('password'),
                    'status' => 'active', 'role' => 'user', 'must_change_password' => true,
                ]);
                $role = Role::where('company_id', $company->id)->where('guard_name', 'web')->where('name', 'Company Admin')->firstOrFail();
                $user->assignRole($role);
            } finally {
                setPermissionsTeamId($previousTeamId);
            }
            $company->featureKeys()->sync($options['feature_keys'] ?? []);
            if ($options['create_subscription'] ?? false) {
                $this->subscriptions->create($company, $options, $request->user());
            }
            $this->audit->record('admin.company.created', $company, [], ['administrator_user_id' => $user->id], $request);

            return $company;
        }, 3);
    }
}
