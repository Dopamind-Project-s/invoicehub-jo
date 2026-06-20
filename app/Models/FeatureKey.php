<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeatureKey extends Model
{
    protected $fillable = ['code', 'name', 'name_ar', 'name_en', 'description', 'category', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class)->withTimestamps();
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_feature_keys')->withTimestamps();
    }
}
