<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('countries')->upsert([['code' => 'JO', 'name_ar' => 'الأردن', 'name_en' => 'Jordan', 'created_at' => now(), 'updated_at' => now()]], ['code']);
        $countryId = DB::table('countries')->where('code', 'JO')->value('id');
        foreach ([['AM', 'عمان', 'Amman'], ['IR', 'إربد', 'Irbid'], ['ZQ', 'الزرقاء', 'Zarqa']] as [$code,$ar,$en]) {
            DB::table('governorates')->updateOrInsert(['code' => $code], ['country_id' => $countryId, 'name_ar' => $ar, 'name_en' => $en, 'updated_at' => now(), 'created_at' => now()]);
        }
        $amman = DB::table('governorates')->where('code', 'AM')->value('id');
        foreach (['Amman', 'Sahab', 'Marj Al Hamam'] as $city) {
            DB::table('cities')->updateOrInsert(['governorate_id' => $amman, 'name_en' => $city], ['name_ar' => $city, 'updated_at' => now(), 'created_at' => now()]);
        }
        foreach ([['JOD', 'Jordanian Dinar', 3], ['USD', 'US Dollar', 2], ['EUR', 'Euro', 2]] as [$c,$n,$m]) {
            DB::table('currencies')->updateOrInsert(['code' => $c], ['name' => $n, 'minor_units' => $m, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]);
        }
        foreach (['PCE' => 'Piece', 'KG' => 'Kilogram', 'GM' => 'Gram', 'LTR' => 'Liter', 'MTR' => 'Meter', 'BOX' => 'Box', 'PACK' => 'Pack'] as $c => $n) {
            DB::table('units')->updateOrInsert(['code' => $c], ['name' => $n, 'updated_at' => now(), 'created_at' => now()]);
        }
        foreach ([['STANDARD', 16, 'S', 'Standard rated'], ['ZERO', 0, 'Z', 'Zero rated'], ['EXEMPT', 0, 'E', 'Tax exempt'], ['OUTSIDE_SCOPE', 0, 'O', 'Outside scope']] as [$c,$r,$tc,$d]) {
            DB::table('tax_categories')->updateOrInsert(['code' => $c], ['tax_rate' => $r, 'tax_code' => $tc, 'description' => $d, 'updated_at' => now(), 'created_at' => now()]);
        }
        foreach (['CASH' => 'Cash', 'RECEIVABLE' => 'Receivable', 'CARD' => 'Card', 'BANK_TRANSFER' => 'Bank Transfer'] as $c => $n) {
            DB::table('payment_methods')->updateOrInsert(['code' => $c], ['name' => $n, 'updated_at' => now(), 'created_at' => now()]);
        }
        foreach (['DRAFT', 'GENERATED', 'SIGNED', 'SUBMITTED', 'ACCEPTED', 'REJECTED'] as $i => $s) {
            DB::table('invoice_statuses')->updateOrInsert(['code' => $s], ['name' => ucfirst(strtolower($s)), 'sort_order' => $i, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
