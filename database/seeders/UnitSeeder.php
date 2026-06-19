<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['PCE' => 'Piece', 'KG' => 'Kilogram', 'GM' => 'Gram', 'LTR' => 'Liter', 'MTR' => 'Meter', 'BOX' => 'Box', 'PACK' => 'Pack'] as $code => $name) {
            DB::table('units')->updateOrInsert(['code' => $code], ['name' => $name, 'description' => $name, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
