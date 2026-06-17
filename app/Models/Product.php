<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'description', 'item_code', 'barcode', 'unit_id', 'tax_category_id', 'default_price', 'is_active'];

    protected $casts = ['default_price' => 'decimal:6', 'is_active' => 'boolean'];
}
