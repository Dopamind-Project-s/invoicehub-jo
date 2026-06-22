<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SERVICE = 'service';

    protected $fillable = ['company_id', 'category_id', 'unit_id', 'tax_category_id', 'tax_profile_id', 'type', 'sku', 'barcode', 'name_ar', 'name_en', 'description', 'image_path', 'item_code', 'default_price', 'price', 'cost', 'is_active'];

    protected $casts = ['default_price' => 'decimal:6', 'price' => 'decimal:6', 'cost' => 'decimal:6', 'is_active' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }
}
