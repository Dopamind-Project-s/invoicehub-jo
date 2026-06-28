<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['users.manage','products.manage','contacts.manage','invoices.view','invoices.create','invoices.approve','invoices.submit','settings.manage','reports.view'] as $name) {
            DB::table('permissions')->updateOrInsert(['name' => $name, 'guard_name' => 'web'], ['updated_at' => now(), 'created_at' => now()]);
        }
    }
}
