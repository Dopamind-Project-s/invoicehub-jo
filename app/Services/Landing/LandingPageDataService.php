<?php

declare(strict_types=1);

namespace App\Services\Landing;

use App\Models\LandingFaq;
use App\Models\Plan;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class LandingPageDataService
{
    public const CACHE_KEY_AR = 'landing:home:ar';

    /** @return array{settings: Collection<string, string|null>, faqs: Collection<int, LandingFaq>, plans: Collection<int, Plan>} */
    public function home(string $locale = 'ar'): array
    {
        if (! Schema::hasTable('site_settings') || ! Schema::hasTable('landing_faqs') || ! Schema::hasTable('plans')) {
            return ['settings' => collect(), 'faqs' => collect(), 'plans' => collect()];
        }

        return Cache::remember(self::CACHE_KEY_AR, now()->addHour(), function (): array {
            return [
                'settings' => $this->settings(),
                'faqs' => LandingFaq::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
                'plans' => Plan::query()->with(['featureKeys' => fn ($query) => $query->where('is_active', true)->orderBy('category')->orderBy('code')])->where('is_active', true)->orderBy('sort_order')->orderBy('monthly_price')->get(),
            ];
        });
    }

    /** @return Collection<string, string|null> */
    private function settings(): Collection
    {
        return SiteSetting::query()
            ->where('is_public', true)
            ->get()
            ->mapWithKeys(fn (SiteSetting $setting): array => ["{$setting->group}.{$setting->key}" => $setting->value]);
    }

    public static function clear(): void
    {
        Cache::forget(self::CACHE_KEY_AR);
    }
}
