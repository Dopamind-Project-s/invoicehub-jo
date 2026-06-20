<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'price', 'monthly_price', 'yearly_price', 'billing_cycle', 'is_active'];

    protected $casts = ['price' => 'decimal:3', 'monthly_price' => 'decimal:3', 'yearly_price' => 'decimal:3', 'is_active' => 'boolean'];

    public function featureKeys(): BelongsToMany
    {
        return $this->belongsToMany(FeatureKey::class)->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
