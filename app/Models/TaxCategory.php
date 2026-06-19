<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxCategory extends Model
{
    protected $fillable = ['code', 'tax_rate', 'tax_code', 'description'];

    protected $casts = ['tax_rate' => 'decimal:6'];
}
