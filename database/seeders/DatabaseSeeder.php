<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            CurrencySeeder::class,
            TaxCategorySeeder::class,
            PaymentMethodSeeder::class,
            RolesAndPermissionsSeeder::class,
            FeatureKeySeeder::class,
            PlanSeeder::class,
            LandingSeeder::class,
            BlogSeeder::class,
        ]);

        foreach (['DRAFT', 'GENERATED', 'SIGNED', 'SUBMITTED', 'ACCEPTED', 'REJECTED', 'ERROR'] as $i => $status) {
            DB::table('invoice_statuses')->updateOrInsert(['code' => $status], ['name' => ucfirst(strtolower($status)), 'sort_order' => $i, 'updated_at' => now(), 'created_at' => now()]);
        }

        $this->call(DemoSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
