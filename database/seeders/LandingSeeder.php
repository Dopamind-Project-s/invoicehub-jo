<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LandingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([SiteSettingSeeder::class, LandingFaqSeeder::class]);
    }
}
