<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('countries')->updateOrInsert(['code' => 'JO'], ['name_en' => 'Jordan', 'name_ar' => 'الأردن', 'updated_at' => now(), 'created_at' => now()]);
    }
}
