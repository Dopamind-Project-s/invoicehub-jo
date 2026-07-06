<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title_ar', 'title_en', 'slug', 'excerpt_ar', 'excerpt_en', 'content_ar', 'content_en', 'image', 'category', 'tags', 'status', 'is_featured', 'published_at', 'views_count', 'meta_title', 'meta_description', 'created_by'])]
class Blog extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function imageUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (! $this->image) { return asset('assets/img/fawtara.png'); }
            return str_starts_with($this->image, 'assets/') ? asset($this->image) : Storage::url($this->image);
        });
    }

    public static function statuses(): array
    {
        return [self::STATUS_DRAFT => 'مسودة', self::STATUS_PUBLISHED => 'منشور'];
    }
}
