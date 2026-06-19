<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['STANDARD', 16, 'S', 'Standard rated'], ['ZERO', 0, 'Z', 'Zero rated'], ['EXEMPT', 0, 'E', 'Exempt'], ['OUTSIDE_SCOPE', 0, 'O', 'Outside scope']] as [$code, $rate, $taxCode, $description]) {
            DB::table('tax_categories')->updateOrInsert(['code' => $code], ['tax_rate' => $rate, 'tax_code' => $taxCode, 'description' => $description, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
