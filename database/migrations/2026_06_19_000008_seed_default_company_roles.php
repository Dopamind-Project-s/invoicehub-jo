<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Company::query()->select('id')->chunkById(100, function ($companies): void {
            foreach ($companies as $company) {
                $this->seedForCompany((int) $company->id);
            }
        });
    }

    public function down(): void
    {
        DB::table('role_has_permissions')->whereNotNull('company_id')->delete();
        DB::table('roles')->whereNotNull('company_id')->delete();
    }

    private function seedForCompany(int $companyId): void
    {
        $permissionSets = [
            'Owner' => ['users.manage', 'products.manage', 'contacts.manage', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit', 'settings.manage', 'reports.view'],
            'Accountant' => ['contacts.manage', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit', 'reports.view'],
            'Reviewer' => ['invoices.view', 'invoices.approve', 'reports.view'],
            'Sales' => ['contacts.manage', 'invoices.view', 'invoices.create'],
            'Viewer' => ['invoices.view', 'reports.view'],
        ];

        foreach ($permissionSets as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web', 'company_id' => $companyId]);
            $ids = Permission::query()->whereIn('name', $permissions)->pluck('id')->all();
            $role->permissions()->syncWithPivotValues($ids, ['company_id' => $companyId]);
        }
    }
};
