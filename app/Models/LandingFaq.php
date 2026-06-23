<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LandingFaq extends Model
{
    protected $fillable = ['question_ar', 'question_en', 'answer_ar', 'answer_en', 'category', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget('landing:home:ar'));
        static::deleted(fn (): bool => Cache::forget('landing:home:ar'));
    }
}
