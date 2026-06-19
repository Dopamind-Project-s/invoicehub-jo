<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $features = [
            ['COMPANY_USERS', 'Company users', 'Manage tenant/company users.'],
            ['PRODUCTS', 'Products', 'Enable products and services catalog.'],
            ['CUSTOMERS', 'Customers', 'Enable customer management.'],
            ['SUPPLIERS', 'Suppliers', 'Enable supplier management.'],
            ['INVOICES', 'Invoices', 'Enable invoice creation and management.'],
            ['PDF_EXPORT', 'PDF export', 'Enable PDF invoice exports.'],
            ['WHATSAPP_SHARE', 'WhatsApp share', 'Enable WhatsApp sharing actions.'],
            ['EMAIL_SHARE', 'Email share', 'Enable email sharing actions.'],
            ['JOFOTARA_SUBMIT', 'JoFotara submit', 'Enable JoFotara invoice submission.'],
            ['JOFOTARA_SYNC', 'JoFotara sync', 'Enable JoFotara status synchronization.'],
            ['ADVANCED_REPORTS', 'Advanced reports', 'Enable advanced reporting.'],
            ['API_ACCESS', 'API access', 'Enable API integrations.'],
            ['AUDIT_LOGS', 'Audit logs', 'Enable audit-log visibility.'],
        ];

        foreach ($features as [$code, $name, $description]) {
            DB::table('feature_keys')->updateOrInsert(
                ['code' => $code],
                ['name' => $name, 'description' => $description, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('feature_keys')->whereIn('code', [
            'COMPANY_USERS', 'PRODUCTS', 'CUSTOMERS', 'SUPPLIERS', 'INVOICES', 'PDF_EXPORT', 'WHATSAPP_SHARE', 'EMAIL_SHARE', 'JOFOTARA_SUBMIT', 'JOFOTARA_SYNC', 'ADVANCED_REPORTS', 'API_ACCESS', 'AUDIT_LOGS',
        ])->delete();
    }
};
