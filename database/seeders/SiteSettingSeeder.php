<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['site', 'app_name', 'InvoSync', 'text', null],
            ['site', 'brand', 'دوبامايند للتحول الرقمي', 'text', 'ar'],
            ['contact', 'phone', '0776079926', 'text', null],
            ['contact', 'whatsapp', '0776079926', 'text', null],
            ['contact', 'email', 'musab.m.alzoubii@gmail.com', 'email', null],
            ['contact', 'person_ar', 'مصعب الزعبي', 'text', 'ar'],
            ['contact', 'person_en', 'Musab Al-zoubi', 'text', 'en'],
            ['contact', 'company_ar', 'دوبامايند', 'text', 'ar'],
            ['contact', 'address_ar', 'الأردن - حلول رقمية للمنشآت', 'textarea', 'ar'],
            ['social', 'linkedin', 'https://www.linkedin.com/in/musabmalzoubi/', 'url', null],
            ['social', 'github', 'https://github.com/MusabAlzoubi', 'url', null],
            ['social', 'facebook', 'https://www.facebook.com/profile.php?id=61562391058375', 'url', null],
            ['social', 'instagram', 'https://www.instagram.com/musab_digitransform', 'url', null],
            ['social', 'instagram_qahwa', 'https://www.instagram.com/qahwa.dopamind', 'url', null],
            ['footer', 'description_ar', 'InvoSync منصة فوترة إلكترونية عربية تساعد المنشآت على إدارة الفواتير والعملاء والمنتجات والاستعداد للربط مع نظام الفوترة الوطني.', 'textarea', 'ar'],
            ['footer', 'copyright_ar', 'جميع الحقوق محفوظة لدوبامايند.', 'text', 'ar'],
            ['seo', 'title_ar', 'InvoSync | نظام فوترة إلكترونية للمنشآت', 'text', 'ar'],
            ['seo', 'description_ar', 'نظام فوترة إلكترونية عربي للمنشآت يدعم إدارة الفواتير والعملاء والمنتجات والربط مع نظام الفوترة الوطني.', 'textarea', 'ar'],
            ['cta', 'primary_text_ar', 'ابدأ إدارة فواتير منشأتك', 'text', 'ar'],
            ['cta', 'secondary_text_ar', 'تواصل عبر واتساب', 'text', 'ar'],
        ];

        foreach ($settings as [$group, $key, $value, $type, $locale]) {
            SiteSetting::updateOrCreate(compact('group', 'key', 'locale'), ['value' => $value, 'type' => $type, 'is_public' => true]);
        }
    }
}
