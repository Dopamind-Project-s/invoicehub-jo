<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Landing\LandingPageDataService;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LandingIntegration extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
    protected static function booted(): void { static::saved(fn (): null => LandingPageDataService::clear()); static::deleted(fn (): null => LandingPageDataService::clear()); }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('integration_icon')->singleFile();
    }
}
