<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LandingFaq;
use App\Models\Plan;
use App\Models\SiteSetting;
use App\Services\Landing\LandingPageDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LandingPageCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_200_with_empty_cache(): void
    {
        $this->seed();
        LandingPageDataService::clear();

        $this->get('/')->assertOk()->assertSee('نظام فوترة إلكترونية للمنشآت');
    }

    public function test_home_returns_200_with_cached_scalar_arrays(): void
    {
        Cache::put(LandingPageDataService::CACHE_KEY_AR, [
            'settings' => ['cta.primary_text_ar' => 'نص كاش', 'contact.whatsapp' => '0776079926'],
            'faqs' => [['id' => 991, 'question_ar' => 'سؤال من الكاش', 'answer_ar' => 'جواب من الكاش']],
            'plans' => [[
                'id' => 991,
                'name' => 'Cached Plan',
                'name_ar' => 'باقة من الكاش',
                'description' => 'Cached description',
                'description_ar' => 'وصف من الكاش',
                'monthly_price' => 7.5,
                'yearly_price' => 75,
                'is_recommended' => true,
                'features' => [['id' => 1, 'name' => 'Feature', 'name_ar' => 'ميزة من الكاش']],
            ]],
        ], now()->addHour());

        $this->get('/')
            ->assertOk()
            ->assertSee('نص كاش')
            ->assertSee('سؤال من الكاش')
            ->assertSee('باقة من الكاش')
            ->assertSee('ميزة من الكاش');
    }

    public function test_old_landing_cache_does_not_crash_page(): void
    {
        $this->seed();
        Cache::put(LandingPageDataService::LEGACY_CACHE_KEY_AR, ['settings' => collect(['cta.primary_text_ar' => 'قديم'])], now()->addHour());

        $this->get('/')->assertOk();
        $this->assertFalse(Cache::has(LandingPageDataService::LEGACY_CACHE_KEY_AR));
    }

    public function test_settings_section_uses_array_data_get(): void
    {
        Cache::put(LandingPageDataService::CACHE_KEY_AR, [
            'settings' => ['cta.primary_text_ar' => 'زر من مصفوفة', 'cta.secondary_text_ar' => 'واتساب من مصفوفة', 'contact.whatsapp' => '0790000000'],
            'faqs' => [],
            'plans' => [],
        ], now()->addHour());

        $this->get('/')->assertOk()->assertSee('زر من مصفوفة')->assertSee('واتساب من مصفوفة');
    }

    public function test_faq_and_pricing_render_from_arrays(): void
    {
        Cache::put(LandingPageDataService::CACHE_KEY_AR, [
            'settings' => [],
            'faqs' => [['id' => 42, 'question_ar' => 'سؤال مصفوفة', 'answer_ar' => 'جواب مصفوفة']],
            'plans' => [[
                'id' => 42,
                'name' => 'Array Plan',
                'name_ar' => 'باقة مصفوفة',
                'description' => 'Array description',
                'description_ar' => 'وصف مصفوفة',
                'monthly_price' => 12,
                'yearly_price' => 120,
                'is_recommended' => false,
                'features' => [['id' => 1, 'name' => 'Array Feature', 'name_ar' => 'ميزة مصفوفة']],
            ]],
        ], now()->addHour());

        $this->get('/')->assertOk()->assertSee('سؤال مصفوفة')->assertSee('باقة مصفوفة')->assertSee('ميزة مصفوفة');
    }

    public function test_landing_service_caches_arrays_and_invalidates_both_cache_keys(): void
    {
        $this->seed();
        $data = app(LandingPageDataService::class)->home('ar');

        $this->assertTrue(Cache::has(LandingPageDataService::CACHE_KEY_AR));
        $this->assertIsArray($data['settings']);
        $this->assertIsArray($data['faqs']);
        $this->assertIsArray($data['plans']);
        $this->assertIsArray($data['plans'][0]['features']);

        Cache::put(LandingPageDataService::LEGACY_CACHE_KEY_AR, ['stale' => true], now()->addHour());
        SiteSetting::query()->firstOrFail()->update(['value' => 'قيمة محدثة']);
        $this->assertFalse(Cache::has(LandingPageDataService::CACHE_KEY_AR));
        $this->assertFalse(Cache::has(LandingPageDataService::LEGACY_CACHE_KEY_AR));

        app(LandingPageDataService::class)->home('ar');
        Cache::put(LandingPageDataService::LEGACY_CACHE_KEY_AR, ['stale' => true], now()->addHour());
        LandingFaq::query()->firstOrFail()->update(['question_ar' => 'سؤال محدث']);
        $this->assertFalse(Cache::has(LandingPageDataService::CACHE_KEY_AR));
        $this->assertFalse(Cache::has(LandingPageDataService::LEGACY_CACHE_KEY_AR));

        app(LandingPageDataService::class)->home('ar');
        Cache::put(LandingPageDataService::LEGACY_CACHE_KEY_AR, ['stale' => true], now()->addHour());
        Plan::query()->firstOrFail()->update(['name_ar' => 'باقة محدثة']);
        $this->assertFalse(Cache::has(LandingPageDataService::CACHE_KEY_AR));
        $this->assertFalse(Cache::has(LandingPageDataService::LEGACY_CACHE_KEY_AR));
    }

    public function test_no_vite_in_landing_views(): void
    {
        foreach (array_merge([resource_path('views/welcome.blade.php')], glob(resource_path('views/landing/**/*.blade.php')) ?: []) as $path) {
            $this->assertStringNotContainsString('@vite', file_get_contents($path), $path);
        }
    }
}
