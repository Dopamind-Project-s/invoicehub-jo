<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Landing\LandingPageDataService;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'locale', 'is_public'];

    protected $casts = ['is_public' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn (): null => LandingPageDataService::clear());
        static::deleted(fn (): null => LandingPageDataService::clear());
    }
}
