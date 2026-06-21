<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@invosync.local'],
            [
                'name' => 'System Administrator',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['company_id' => null, 'name' => 'Super Admin', 'guard_name' => 'web'],
            ['updated_at' => $now, 'created_at' => $now]
        );

        $userId = DB::table('users')->where('email', 'admin@invosync.local')->value('id');
        $roleId = DB::table('roles')->whereNull('company_id')->where('name', 'Super Admin')->where('guard_name', 'web')->value('id');

        if ($userId && $roleId) {
            DB::table('model_has_roles')->updateOrInsert([
                'role_id' => $roleId,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId,
                'company_id' => 0,
            ]);
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
