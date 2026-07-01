<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['PCE','قطعة','Piece'],['MEAL','وجبة','Meal'],['KG','كيلو','Kilogram'],['HOUR','ساعة','Hour'],['SERVICE','خدمة','Service'],['LTR','لتر','Liter']] as [$code,$ar,$en]) {
            DB::table('units')->updateOrInsert(['company_id' => null, 'code' => $code], ['name'=>$en,'name_ar'=>$ar,'name_en'=>$en,'symbol'=>$code,'description'=>$ar,'is_active'=>true,'updated_at'=>now(),'created_at'=>now()]);
        }
        $company = Company::where('tax_number', '9578331')->first();
        if ($company) foreach ([['PCE','قطعة','Piece'],['MEAL','وجبة','Meal'],['KG','كيلو','Kilogram'],['HOUR','ساعة','Hour'],['SERVICE','خدمة','Service']] as [$code,$ar,$en]) {
            DB::table('units')->updateOrInsert(['company_id' => $company->id, 'code' => $code], ['name'=>$en,'name_ar'=>$ar,'name_en'=>$en,'symbol'=>$code,'description'=>$ar,'is_active'=>true,'updated_at'=>now(),'created_at'=>now()]);
        }
    }
}
