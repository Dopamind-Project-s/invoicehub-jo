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
            ['site', 'app_name', 'InvoSync', 'text', null], ['site', 'brand', 'دوبامايند للتحول الرقمي', 'text', 'ar'], ['site', 'brand_short', 'دوبامايند', 'text', 'ar'], ['site', 'product', 'InvoSync', 'text', null],
            ['person', 'name_ar', 'مصعب الزعبي', 'text', 'ar'], ['person', 'name_en', 'Musab Al-zoubi', 'text', 'en'],
            ['contact', 'phone', '0776079926', 'text', null], ['contact', 'whatsapp', '0776079926', 'text', null], ['contact', 'email', 'musab.m.alzoubii@gmail.com', 'email', null], ['contact', 'address_ar', 'الأردن - حلول رقمية للمنشآت', 'textarea', 'ar'],
            ['social', 'linkedin', 'https://www.linkedin.com/in/musabmalzoubi/', 'url', null], ['social', 'github', 'https://github.com/MusabAlzoubi', 'url', null], ['social', 'facebook', 'https://www.facebook.com/profile.php?id=61562391058375', 'url', null], ['social', 'instagram_personal', 'https://www.instagram.com/musab_digitransform', 'url', null], ['social', 'instagram', 'https://www.instagram.com/qahwa.dopamind', 'url', null],
            ['seo', 'meta_title_ar', 'InvoSync | نظام فوترة إلكترونية للمنشآت', 'text', 'ar'], ['seo', 'meta_description_ar', 'نظام فوترة إلكترونية عربي للمنشآت يدعم إدارة الفواتير والعملاء والمنتجات والربط مع نظام الفوترة الوطني.', 'textarea', 'ar'], ['seo','keywords_ar','فوترة إلكترونية, جوفوتارا, فواتير الأردن, InvoSync','text','ar'], ['seo','robots','index,follow','text',null], ['seo','schema_json_ld','{"@context":"https://schema.org","@type":"SoftwareApplication","name":"InvoSync"}','textarea',null],
            ['theme','primary_color','#0ea5e9','color',null], ['theme','primary_dark','#0284c7','color',null], ['theme','cyan','#06b6d4','color',null], ['theme','teal','#14b8a6','color',null], ['theme','gradient_start','#0ea5e9','color',null], ['theme','gradient_end','#06b6d4','color',null], ['theme','dark_bg','#0f172a','color',null], ['theme','light_bg','#f8fafc','color',null], ['theme','button_style','rounded','text',null], ['theme','border_radius','24px','text',null],
            ['cta', 'primary_text_ar', 'ابدأ إدارة فواتير منشأتك', 'text', 'ar'], ['cta', 'secondary_text_ar', 'تواصل عبر واتساب', 'text', 'ar'],
        ];

        foreach ($settings as [$group, $key, $value, $type, $locale]) {
            SiteSetting::updateOrCreate(compact('group', 'key', 'locale'), ['value' => $value, 'type' => $type, 'is_public' => true]);
        }
    }
}
