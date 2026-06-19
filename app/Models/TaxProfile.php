<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxProfile extends Model
{
    protected $fillable = ['company_id', 'name', 'tax_type', 'tax_percent', 'jofotara_tax_code', 'is_default', 'is_active'];

    protected $casts = ['tax_percent' => 'decimal:6', 'is_default' => 'boolean', 'is_active' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
