<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'locale', 'is_public'];

    protected $casts = ['is_public' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget('landing:home:ar'));
        static::deleted(fn (): bool => Cache::forget('landing:home:ar'));
    }
}
