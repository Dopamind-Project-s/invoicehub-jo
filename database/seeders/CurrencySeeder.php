<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['JOD', 'Jordanian Dinar', 3], ['USD', 'US Dollar', 2], ['EUR', 'Euro', 2]] as [$code, $name, $minor]) {
            DB::table('currencies')->updateOrInsert(['code' => $code], ['name' => $name, 'minor_units' => $minor, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
