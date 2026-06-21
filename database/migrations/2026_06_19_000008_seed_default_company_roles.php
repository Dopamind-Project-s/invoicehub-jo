<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ROLE_PERMISSIONS = [
        'Owner' => ['users.manage', 'products.manage', 'contacts.manage', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit', 'settings.manage', 'reports.view'],
        'Accountant' => ['contacts.manage', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit', 'reports.view'],
        'Reviewer' => ['invoices.view', 'invoices.approve', 'reports.view'],
        'Sales' => ['contacts.manage', 'invoices.view', 'invoices.create'],
        'Viewer' => ['invoices.view', 'reports.view'],
    ];

    public function up(): void
    {
        foreach (array_unique(array_merge(...array_values(self::ROLE_PERMISSIONS))) as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        DB::table('companies')->select('id')->orderBy('id')->chunkById(100, function ($companies): void {
            foreach ($companies as $company) {
                $this->seedForCompany((int) $company->id);
            }
        });

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        // Keep authorization data to avoid data loss on rollback.
    }

    private function seedForCompany(int $companyId): void
    {
        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            DB::table('roles')->updateOrInsert(
                ['company_id' => $companyId, 'name' => $roleName, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );

            $roleId = DB::table('roles')->where('company_id', $companyId)->where('name', $roleName)->where('guard_name', 'web')->value('id');
            $permissionIds = DB::table('permissions')->whereIn('name', $permissions)->where('guard_name', 'web')->pluck('id');

            DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }
};
