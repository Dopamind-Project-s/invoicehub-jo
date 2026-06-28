<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // System Seeders
        $this->call([
            CountrySeeder::class,
            CurrencySeeder::class,
            TaxCategorySeeder::class,
            PaymentMethodSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            FeatureKeySeeder::class,
            PlanSeeder::class,
            LandingSeeder::class,
        ]);

        foreach (['DRAFT', 'GENERATED', 'SIGNED', 'SUBMITTED', 'ACCEPTED', 'REJECTED', 'ERROR'] as $i => $status) {
            DB::table('invoice_statuses')->updateOrInsert(['code' => $status], ['name' => ucfirst(strtolower($status)), 'sort_order' => $i, 'updated_at' => now(), 'created_at' => now()]);
        }

        // Company Seeders
        $this->call([
            CompanySeeder::class,
            CompanyUserSeeder::class,
            SubscriptionSeeder::class,
        ]);

        // Master Data
        $this->call([
            ProductCategorySeeder::class,
            UnitSeeder::class,
            TaxProfileSeeder::class,
            InvoiceTemplateSeeder::class,
        ]);

        // Demo Data
        $this->call([
            CustomerSeeder::class,
            ContactSeeder::class,
            ProductSeeder::class,
            InvoiceSeeder::class,
        ]);

        // Sample Data
        $this->call([
            SubscriptionHistorySeeder::class,
            SubscriptionRequestSeeder::class,
        ]);
    }
}
