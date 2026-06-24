<?php

declare(strict_types=1);

namespace App\Services\Landing;

use App\Models\{LandingFaq,LandingHeroSlide,LandingIntegration,LandingPartner,LandingStatistic,LandingTestimonial,Plan,SiteSetting};
use Illuminate\Support\Arr; use Illuminate\Support\Facades\Cache; use Illuminate\Support\Facades\Schema;

class LandingPageDataService
{
    public const CACHE_KEY_AR = 'landing:home:v2:ar'; public const LEGACY_CACHE_KEY_AR = 'landing:home:ar';
    public function home(string $locale = 'ar'): array
    {
        Cache::forget(self::LEGACY_CACHE_KEY_AR);
        foreach (['site_settings','landing_faqs','plans'] as $t) if (! Schema::hasTable($t)) return ['settings'=>[],'faqs'=>[],'plans'=>[],'heroSlides'=>[],'testimonials'=>[],'integrations'=>[],'statistics'=>[],'partners'=>[]];
        $data = Cache::remember(self::CACHE_KEY_AR, now()->addHour(), fn(): array => ['settings'=>$this->settings(),'faqs'=>$this->faqs(),'plans'=>$this->plans(),'heroSlides'=>$this->heroSlides(),'testimonials'=>$this->testimonials(),'integrations'=>$this->integrations(),'statistics'=>$this->statistics(),'partners'=>$this->partners()]);
        $data['settings'] = $this->settingsForDataGet($data['settings'] ?? []); return $data;
    }
    private function settingsForDataGet(array $settings): array { return array_replace_recursive(Arr::undot($settings), $settings); }
    private function settings(): array { return SiteSetting::where('is_public',true)->get(['group','key','value'])->mapWithKeys(fn($s)=>["$s->group.$s->key"=>$s->value])->all(); }
    private function faqs(): array { return LandingFaq::where('is_active',true)->orderBy('sort_order')->orderBy('id')->get(['id','question_ar','answer_ar','category','sort_order'])->map(fn($x)=>$x->toArray())->all(); }
    private function plans(): array { return Plan::with(['featureKeys'=>fn($q)=>$q->where('is_active',true)->orderBy('category')->orderBy('code')])->where('is_active',true)->orderBy('sort_order')->orderBy('monthly_price')->get()->map(fn(Plan $p)=>['id'=>(int)$p->id,'name'=>$p->name,'name_ar'=>$p->name_ar,'description'=>$p->description,'description_ar'=>$p->description_ar,'monthly_price'=>(float)$p->monthly_price,'yearly_price'=>(float)$p->yearly_price,'is_recommended'=>(bool)$p->is_recommended,'features'=>$p->featureKeys->map(fn($f)=>['id'=>(int)$f->id,'name'=>$f->name,'name_ar'=>$f->name_ar])->all()])->all(); }
    private function heroSlides(): array { if (!Schema::hasTable('landing_hero_slides')) return []; return LandingHeroSlide::where('is_active',true)->orderBy('sort_order')->orderBy('id')->get()->map(fn($x)=>['id'=>$x->id,'title_ar'=>$x->title_ar,'subtitle_ar'=>$x->subtitle_ar,'primary_cta_text_ar'=>$x->primary_cta_text_ar,'primary_cta_url'=>$x->primary_cta_url,'secondary_cta_text_ar'=>$x->secondary_cta_text_ar,'secondary_cta_url'=>$x->secondary_cta_url,'background_url'=>$x->getFirstMediaUrl('hero_background'),'dashboard_url'=>$x->getFirstMediaUrl('hero_dashboard')])->all(); }
    private function testimonials(): array { if (!Schema::hasTable('landing_testimonials')) return []; return LandingTestimonial::where('is_active',true)->orderBy('sort_order')->orderBy('id')->get()->map(fn($x)=>['name'=>$x->name,'establishment'=>$x->establishment,'position'=>$x->position,'testimonial_ar'=>$x->testimonial_ar,'rating'=>(int)$x->rating,'avatar_url'=>$x->getFirstMediaUrl('testimonial_avatar')])->all(); }
    private function integrations(): array { if (!Schema::hasTable('landing_integrations')) return []; return LandingIntegration::where('is_active',true)->orderBy('sort_order')->orderBy('id')->get()->map(fn($x)=>['name_ar'=>$x->name_ar,'description_ar'=>$x->description_ar,'icon'=>$x->icon,'status'=>$x->status,'icon_url'=>$x->getFirstMediaUrl('integration_icon')])->all(); }
    private function statistics(): array { if (!Schema::hasTable('landing_statistics')) return []; return LandingStatistic::where('is_active',true)->orderBy('sort_order')->orderBy('id')->get(['label_ar','value','suffix','icon'])->map(fn($x)=>$x->toArray())->all(); }
    private function partners(): array { if (!Schema::hasTable('landing_partners')) return []; return LandingPartner::where('is_active',true)->orderBy('sort_order')->orderBy('id')->get()->map(fn($x)=>['name_ar'=>$x->name_ar,'url'=>$x->url,'logo_url'=>$x->getFirstMediaUrl('partner_logo')])->all(); }
    public static function clear(): void { Cache::forget(self::CACHE_KEY_AR); Cache::forget(self::LEGACY_CACHE_KEY_AR); }
}
