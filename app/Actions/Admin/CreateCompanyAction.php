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
use Spatie\Permission\Models\Role;

class CreateCompanyAction
{
    public function __construct(private readonly CompanyRoleSeeder $roles, private readonly AdminSubscriptionService $subscriptions, private readonly AuditLogger $audit) {}

    public function execute(array $companyData, array $options, Request $request): Company
    {
        return DB::transaction(function () use ($companyData, $options, $request): Company {
            $company = Company::create($companyData);
            $this->roles->seed($company);
            setPermissionsTeamId($company->id);
            $user = User::create([
                'company_id' => $company->id,
                'name' => $company->name_ar ?: $company->legal_name_ar ?: $company->name_en ?: "Company #{$company->id}",
                'email' => mb_strtolower(trim($company->email)),
                'password' => Hash::make('password'),
                'status' => 'active', 'role' => 'user', 'must_change_password' => true,
            ]);
            $role = Role::where('company_id', $company->id)->where('name', 'Company Admin')->firstOrFail();
            $user->assignRole($role);
            $company->featureKeys()->sync($options['feature_keys'] ?? []);
            if ($options['create_subscription'] ?? false) {
                $this->subscriptions->create($company, $options, $request->user());
            }
            $this->audit->record('admin.company.created', $company, [], ['administrator_user_id' => $user->id], $request);

            return $company;
        }, 3);
    }
}
