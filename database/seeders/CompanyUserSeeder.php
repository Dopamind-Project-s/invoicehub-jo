<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = DB::table('companies')->where('tax_number', '9578331')->first() ?? DB::table('companies')->orderBy('id')->first();
        if (! $company) {
            return;
        }

        $this->ensureDefaultRoles((int) $company->id);

        DB::table('users')->updateOrInsert(
            ['email' => 'company@invosync.local'],
            [
                'company_id' => $company->id,
                'name' => 'Company User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $userId = DB::table('users')->where('email', 'company@invosync.local')->value('id');
        $roleId = DB::table('roles')->where('company_id', $company->id)->where('name', 'Owner')->where('guard_name', 'web')->value('id');

        if ($userId && $roleId) {
            DB::table('model_has_roles')->updateOrInsert([
                'role_id' => $roleId,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId,
                'company_id' => $company->id,
            ]);
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    private function ensureDefaultRoles(int $companyId): void
    {
        $rolePermissions = [
            'Owner' => ['users.manage', 'products.manage', 'contacts.manage', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit', 'settings.manage', 'reports.view'],
            'Accountant' => ['contacts.manage', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit', 'reports.view'],
            'Reviewer' => ['invoices.view', 'invoices.approve', 'reports.view'],
            'Sales' => ['contacts.manage', 'invoices.view', 'invoices.create'],
            'Viewer' => ['invoices.view', 'reports.view'],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            DB::table('roles')->updateOrInsert(
                ['company_id' => $companyId, 'name' => $roleName, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
            $roleId = DB::table('roles')->where('company_id', $companyId)->where('name', $roleName)->where('guard_name', 'web')->value('id');
            $permissionIds = DB::table('permissions')->whereIn('name', $permissions)->where('guard_name', 'web')->pluck('id');
            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }
    }
}
