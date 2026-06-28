<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->first();
        if (! $company) return;
        foreach ([
            ['customer','عميل نقدي','Cash Customer',null,'990000001'],
            ['customer','منشأة تجريبية','Demo Establishment','300000001','200000001'],
            ['supplier','مورد مواد غذائية','Food Supplies Vendor','1234567',null],
            ['customer','عميل ضريبي','Tax Customer','9876543','200000004'],
        ] as [$type,$ar,$en,$tax,$national]) {
            Contact::updateOrCreate(['company_id'=>$company->id,'name_ar'=>$ar], ['type'=>$type,'name_en'=>$en,'tax_number'=>$tax,'national_number'=>$national,'phone'=>'0790000000','email'=>null,'address'=>'عمّان - الأردن','city'=>'عمّان','country'=>'JO','is_active'=>true]);
        }
    }
}
