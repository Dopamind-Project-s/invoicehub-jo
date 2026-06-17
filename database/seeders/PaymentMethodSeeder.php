<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['cash' => 'Cash', 'receivable' => 'Receivable'] as $code => $name) {
            DB::table('payment_methods')->updateOrInsert(['code' => $code], ['name' => $name, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
