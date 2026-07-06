<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            InvoiceTemplateSeeder::class,
            CompanyUserSeeder::class,
            SubscriptionSeeder::class,
            ProductCategorySeeder::class,
            UnitSeeder::class,
            TaxProfileSeeder::class,
            CustomerSeeder::class,
            ContactSeeder::class,
            ProductSeeder::class,
            InvoiceSeeder::class,
            SubscriptionHistorySeeder::class,
            SubscriptionRequestSeeder::class,
        ]);
    }
}
