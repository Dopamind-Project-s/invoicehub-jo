<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Landing\LandingPageDataService;
use Illuminate\Database\Eloquent\Model;

class LandingStatistic extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
    protected static function booted(): void { static::saved(fn (): null => LandingPageDataService::clear()); static::deleted(fn (): null => LandingPageDataService::clear()); }
}
