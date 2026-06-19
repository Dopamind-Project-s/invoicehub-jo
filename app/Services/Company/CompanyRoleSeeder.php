<?php

declare(strict_types=1);

namespace App\Services\Company;

use App\Models\Company;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CompanyRoleSeeder
{
    public const DEFAULT_ROLES = [
        'Owner' => ['users.manage', 'products.manage', 'contacts.manage', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit', 'settings.manage', 'reports.view'],
        'Accountant' => ['contacts.manage', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit', 'reports.view'],
        'Reviewer' => ['invoices.view', 'invoices.approve', 'reports.view'],
        'Sales' => ['contacts.manage', 'invoices.view', 'invoices.create'],
        'Viewer' => ['invoices.view', 'reports.view'],
    ];

    public function seed(Company $company): void
    {
        foreach (self::DEFAULT_ROLES as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web', 'company_id' => $company->id]);
            $permissionIds = Permission::query()->whereIn('name', $permissions)->pluck('id')->all();
            $role->permissions()->syncWithPivotValues($permissionIds, ['company_id' => $company->id]);
        }
    }
}
