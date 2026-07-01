<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TaxCategory;
use App\Models\TaxProfile;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->first();
        if (! $company) return;
        $zeroCat = TaxCategory::where('code', 'ZERO')->firstOrFail();
        $zeroProfile = TaxProfile::where('company_id',$company->id)->where('name','ضريبة 0%')->first();
        $rows = [
            ['MANSAF','وجبة منسف لحم بلدي','Mansaf meal','MEALS','MEAL','product',8.000],
            ['MIXED-GRILL','وجبة مشاوي مشكلة','Mixed grill meal','MEALS','MEAL','product',10.000],
            ['WATER-BOTTLE','عبوة مياه','Water bottle','DRINKS','PCE','product',0.250],
            ['INVOICE-PREP','خدمة تجهيز فاتورة','Invoice preparation service','SERVICES','SERVICE','service',2.000],
            ['DELIVERY','خدمة توصيل','Delivery service','SERVICES','SERVICE','service',1.000],
        ];
        foreach ($rows as [$sku,$ar,$en,$cat,$unit,$type,$price]) {
            Product::updateOrCreate(['company_id'=>$company->id,'sku'=>$sku], [
                'item_code'=>$sku,'name_ar'=>$ar,'name_en'=>$en,'description'=>$ar,'category_id'=>ProductCategory::where('company_id',$company->id)->where('code',$cat)->value('id'),
                'unit_id'=>Unit::where('company_id',$company->id)->where('code',$unit)->value('id') ?: Unit::whereNull('company_id')->where('code',$unit)->value('id'),
                'tax_category_id'=>$zeroCat->id,'tax_profile_id'=>$zeroProfile?->id,'type'=>$type,'default_price'=>$price,'price'=>$price,'is_active'=>true,
            ]);
        }
    }
}
