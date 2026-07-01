<?php

declare(strict_types=1);

namespace App\Services\Landing;

use App\Models\FeatureKey;
use App\Models\LandingFaq;
use App\Models\Plan;
use App\Models\SiteSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LandingPageDataService
{
    public const CACHE_KEY_AR = 'landing:home:v3:ar';
    public const LEGACY_CACHE_KEY_AR = 'landing:home:ar';

    /** @return array{settings: array<string, string|null>, faqs: array<int, array<string, mixed>>, plans: array<int, array<string, mixed>>} */
    public function home(string $locale = 'ar'): array
    {
        Cache::forget(self::LEGACY_CACHE_KEY_AR);

        if (! Schema::hasTable('site_settings') || ! Schema::hasTable('landing_faqs') || ! Schema::hasTable('plans')) {
            return ['settings' => [], 'faqs' => [], 'plans' => [], 'allFeatures' => []];
        }

        /** @var array{settings: array<string, string|null>, faqs: array<int, array<string, mixed>>, plans: array<int, array<string, mixed>>} $data */
        $data = Cache::remember(self::CACHE_KEY_AR, now()->addHour(), fn (): array => [
            'settings' => $this->settings(),
            'faqs' => $this->faqs(),
            'plans' => $this->plans(),
            'allFeatures' => $this->features(),
        ]);

        $data['settings'] = $this->settingsForDataGet($data['settings'] ?? []);

        return $data;
    }

    /** @param array<string, string|null> $settings */
    private function settingsForDataGet(array $settings): array
    {
        return array_replace_recursive(Arr::undot($settings), $settings);
    }

    /** @return array<string, string|null> */
    private function settings(): array
    {
        return SiteSetting::query()
            ->where('is_public', true)
            ->get(['group', 'key', 'value'])
            ->mapWithKeys(fn (SiteSetting $setting): array => ["{$setting->group}.{$setting->key}" => $setting->value])
            ->all();
    }

    /** @return array<int, array{id: int, question_ar: string|null, answer_ar: string|null, category: string|null, sort_order: int|null}> */
    private function faqs(): array
    {
        return LandingFaq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'question_ar', 'answer_ar', 'category', 'sort_order'])
            ->map(fn (LandingFaq $faq): array => [
                'id' => (int) $faq->id,
                'question_ar' => $faq->question_ar,
                'answer_ar' => $faq->answer_ar,
                'category' => $faq->category,
                'sort_order' => $faq->sort_order === null ? null : (int) $faq->sort_order,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function plans(): array
    {
        return Plan::query()
            ->with(['featureKeys' => fn ($query) => $query->where('is_active', true)->orderBy('category')->orderBy('code')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('plan_rank')
            ->orderBy('monthly_price')
            ->get()
            ->map(fn (Plan $plan): array => [
                'id' => (int) $plan->id,
                'name' => $plan->name,
                'name_ar' => $plan->name_ar,
                'description' => $plan->description,
                'description_ar' => $plan->description_ar,
                'monthly_price' => (float) $plan->monthly_price,
                'yearly_price' => (float) $plan->yearly_price,
                'is_recommended' => (bool) $plan->is_recommended,
                'feature_ids' => $plan->featureKeys->pluck('id')->map(fn ($id) => (int) $id)->all(),
                'features' => $plan->featureKeys->map(fn ($feature): array => [
                    'id' => (int) $feature->id,
                    'name' => $feature->name,
                    'name_ar' => $feature->name_ar,
                ])->all(),
            ])
            ->all();
    }

    private function features(): array
    {
        return FeatureKey::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('code')
            ->get(['id', 'name', 'name_ar'])
            ->map(fn (FeatureKey $feature): array => [
                'id' => (int) $feature->id,
                'name' => $feature->name,
                'name_ar' => $feature->name_ar,
            ])
            ->all();
    }

    public static function clear(): void
    {
        Cache::forget(self::CACHE_KEY_AR);
        Cache::forget(self::LEGACY_CACHE_KEY_AR);
    }
}
