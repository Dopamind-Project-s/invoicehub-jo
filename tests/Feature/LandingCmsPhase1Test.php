<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LandingFaq;
use App\Models\Plan;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Landing\LandingPageDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LandingCmsPhase1Test extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_returns_ok_and_reads_pricing_from_plans(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('نظام فوترة إلكترونية للمنشآت')
            ->assertSee(Plan::where('slug', 'professional')->value('name_ar'));
    }

    public function test_landing_page_reads_active_faqs_only(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('هل InvoSync مناسب للمنشآت الصغيرة؟')
            ->assertDontSee('سؤال غير منشور');
    }

    public function test_site_settings_are_seeded(): void
    {
        $this->seed();

        $this->assertDatabaseHas('site_settings', ['group' => 'contact', 'key' => 'phone', 'value' => '0776079926']);
        $this->assertDatabaseHas('site_settings', ['group' => 'site', 'key' => 'brand', 'value' => 'دوبامايند للتحول الرقمي']);
    }

    public function test_admin_faq_crud_is_protected_by_super_admin(): void
    {
        $this->seed();

        $this->get(route('admin.landing-cms.faqs.index'))->assertRedirect(route('login'));

        $admin = User::where('email', 'admin@invosync.local')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.landing-cms.faqs.store'), [
            'question_ar' => 'هل توجد إدارة للأسئلة؟',
            'answer_ar' => 'نعم، يمكن للمدير العام إدارتها من لوحة التحكم.',
            'category' => 'admin',
            'sort_order' => 10,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('landing_faqs', ['question_ar' => 'هل توجد إدارة للأسئلة؟']);
    }

    public function test_landing_cache_works_and_clears_on_updates(): void
    {
        $this->seed();

        app(LandingPageDataService::class)->home('ar');
        $this->assertTrue(Cache::has(LandingPageDataService::CACHE_KEY_AR));

        LandingFaq::query()->firstOrFail()->update(['question_ar' => 'سؤال محدث للكاش']);
        $this->assertFalse(Cache::has(LandingPageDataService::CACHE_KEY_AR));

        app(LandingPageDataService::class)->home('ar');
        SiteSetting::query()->firstOrFail()->update(['value' => 'قيمة محدثة']);
        $this->assertFalse(Cache::has(LandingPageDataService::CACHE_KEY_AR));
    }

    public function test_no_vite_in_welcome_or_landing_views(): void
    {
        foreach (array_merge([resource_path('views/welcome.blade.php')], glob(resource_path('views/landing/**/*.blade.php')) ?: []) as $path) {
            $this->assertStringNotContainsString('@vite', file_get_contents($path));
        }
    }
}
