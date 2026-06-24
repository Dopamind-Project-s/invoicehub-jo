<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            CurrencySeeder::class,
            UnitSeeder::class,
            TaxCategorySeeder::class,
            PaymentMethodSeeder::class,
            FeatureKeySeeder::class,
            CompanySeeder::class,
            PlanSeeder::class,
            SiteSettingSeeder::class,
            LandingFaqSeeder::class,
            LandingPhase2Seeder::class,
            SuperAdminSeeder::class,
            CompanyUserSeeder::class,
            InvoiceTemplateSeeder::class,
            CustomerSeeder::class,
            ContactSeeder::class,
            ProductSeeder::class,
            InvoiceSeeder::class,
        ]);

        foreach (['DRAFT', 'GENERATED', 'SIGNED', 'SUBMITTED', 'ACCEPTED', 'REJECTED', 'ERROR'] as $i => $status) {
            DB::table('invoice_statuses')->updateOrInsert(['code' => $status], ['name' => ucfirst(strtolower($status)), 'sort_order' => $i, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
