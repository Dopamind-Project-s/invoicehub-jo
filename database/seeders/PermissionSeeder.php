<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public const GROUPS = [
        'Dashboard' => ['view_admin_dashboard', 'view_company_dashboard'],
        'Companies / Tenants' => ['manage_companies'],
        'Subscription Requests' => ['manage_subscription_requests'],
        'Plans / Subscriptions' => ['manage_plans', 'manage_subscriptions'],
        'Users' => ['manage_users', 'manage_company_users', 'users.manage'],
        'Roles / Permissions' => ['manage_roles'],
        'Invoices' => ['manage_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices', 'invoices.view', 'invoices.create', 'invoices.approve', 'invoices.submit'],
        'Customers' => ['view_customers', 'manage_customers', 'contacts.manage'],
        'Products' => ['view_products', 'manage_products', 'products.manage'],
        'Reports' => ['view_reports', 'reports.view'],
        'Settings' => ['manage_company_settings', 'settings.manage'],
        'JoFotara Integration' => ['manage_jofotara_credentials'],
        'Audit Logs' => ['view_audit_logs'],
    ];

    public function run(): void
    {
        $now = now();
        foreach (collect(self::GROUPS)->flatten()->unique() as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
