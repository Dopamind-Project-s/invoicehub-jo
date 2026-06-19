<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::updateOrCreate(['name' => 'عميل نقدي'], ['customer_type' => 'INDIVIDUAL', 'tax_number' => null, 'national_number' => null, 'country_code' => 'JO', 'city' => 'Jordan', 'address' => 'Jordan', 'postal_code' => '-', 'phone' => '-', 'is_taxable' => true]);
    }
}
