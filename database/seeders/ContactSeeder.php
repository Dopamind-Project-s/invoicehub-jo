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
            ['customer', 'عميل نقدي', 'Cash Customer', null, '990000001'],
            ['supplier', 'مورد رئيسي', 'Main Supplier', '1234567', null],
            ['both', 'عميل ومورد تجريبي', 'Demo Partner', '7654321', '200000001'],
        ] as [$type, $ar, $en, $tax, $national]) {
            Contact::updateOrCreate(['company_id' => $company->id, 'name_ar' => $ar], ['type' => $type, 'name_en' => $en, 'tax_number' => $tax, 'national_number' => $national, 'phone' => '0790000000', 'email' => null, 'address' => 'الأردن', 'city' => 'عمّان', 'country' => 'JO', 'is_active' => true]);
        }
    }
}
