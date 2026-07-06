<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Company\CompanyRoleSeeder;
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

        app(CompanyRoleSeeder::class)->seed(\App\Models\Company::findOrFail((int) $company->id));

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

}