<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\TaxCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->first();
        $unit = Unit::where('code', 'PCE')->firstOrFail();
        $tax = TaxCategory::where('code', 'ZERO')->firstOrFail();
        foreach ([['MANSAF', 'وجبة منسف لحم بلدي', 'Mansaf'], ['MATRIX', 'ماتريكس', 'Matrix'], ['DELIVERY', 'توصيل', 'Delivery']] as [$code, $ar, $en]) {
            Product::updateOrCreate(['company_id' => $company?->id, 'item_code' => $code], ['name_ar' => $ar, 'name_en' => $en, 'company_id' => $company?->id, 'sku' => $code, 'type' => 'product', 'description' => $ar, 'unit_id' => $unit->id, 'tax_category_id' => $tax->id, 'default_price' => 0, 'is_active' => true]);
        }
    }
}
