<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Landing\LandingPageDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['name', 'name_ar', 'name_en', 'slug', 'description', 'description_ar', 'description_en', 'price', 'monthly_price', 'yearly_price', 'billing_cycle', 'sort_order', 'is_active', 'is_recommended'];

    protected $casts = ['price' => 'decimal:3', 'monthly_price' => 'decimal:3', 'yearly_price' => 'decimal:3', 'is_active' => 'boolean', 'is_recommended' => 'boolean', 'sort_order' => 'integer'];

    protected static function booted(): void
    {
        static::saved(fn (): null => LandingPageDataService::clear());
        static::deleted(fn (): null => LandingPageDataService::clear());
    }

    public function featureKeys(): BelongsToMany
    {
        return $this->belongsToMany(FeatureKey::class)->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
