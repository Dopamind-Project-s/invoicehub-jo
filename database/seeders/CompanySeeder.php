<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'legal_name_ar' => 'مطبخ ومشاوي جوهرة الجبيهة',
            'legal_name_en' => 'Jawharat Al Jubaiha Kitchen and Grills',
            'trade_name' => 'مطبخ ومشاوي جوهرة الجبيهة',
            'tax_number' => '9578331',
            'jofotara_source_id' => '18412122',
            'phone' => '799021616',
            'country_code' => 'JO',
            'city' => 'Jordan',
            'street' => 'Jordan',
            'default_currency' => 'JOD',
            'icv_prefix' => 'INV',
            'is_active' => true,
        ];
        if (filled(env('JOFOTARA_CLIENT_ID'))) {
            $data['jofotara_client_id'] = env('JOFOTARA_CLIENT_ID');
        }

        if (filled(env('JOFOTARA_SECRET_KEY'))) {
            $data['jofotara_secret_key'] = env('JOFOTARA_SECRET_KEY');
        }
        $company = Company::updateOrCreate(['tax_number' => '9578331'], $data);

        $featureIds = \App\Models\FeatureKey::query()->where('is_active', true)->pluck('id')->all();
        if ($featureIds !== []) {
            $company->featureKeys()->sync($featureIds);
        }
    }
}
