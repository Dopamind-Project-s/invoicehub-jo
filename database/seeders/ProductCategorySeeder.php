<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->first();
        if (! $company) return;
        foreach ([['MEALS','وجبات','Meals','🍽️'],['DRINKS','مشروبات','Drinks','🥤'],['SERVICES','خدمات','Services','🧾'],['FOOD','مواد غذائية','Food supplies','🥫']] as [$code,$ar,$en,$icon]) {
            ProductCategory::updateOrCreate(['company_id'=>$company->id,'code'=>$code], ['name_ar'=>$ar,'name_en'=>$en,'description'=>$ar,'icon'=>$icon,'is_active'=>true]);
        }
    }
}
