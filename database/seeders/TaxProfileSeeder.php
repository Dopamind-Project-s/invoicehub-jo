<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\TaxProfile;
use Illuminate\Database\Seeder;

class TaxProfileSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->first();
        if (! $company) return;
        foreach ([['معفى',0,'E',false],['ضريبة 0%',0,'Z',true],['ضريبة 16%',16,'S',false]] as [$name,$rate,$code,$default]) {
            TaxProfile::updateOrCreate(['company_id'=>$company->id,'name'=>$name], ['tax_type'=>'sales','tax_percent'=>$rate,'jofotara_tax_code'=>$code,'is_default'=>$default,'is_active'=>true]);
        }
    }
}
