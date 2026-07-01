<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Landing\LandingPageDataService;
use Illuminate\Database\Eloquent\Model;

class LandingFaq extends Model
{
    protected $fillable = ['question_ar', 'question_en', 'answer_ar', 'answer_en', 'category', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(fn (): null => LandingPageDataService::clear());
        static::deleted(fn (): null => LandingPageDataService::clear());
    }
}
