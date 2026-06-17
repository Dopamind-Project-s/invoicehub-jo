<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['customer_type', 'name', 'tax_number', 'national_number', 'country_code', 'city', 'address', 'phone', 'email', 'is_taxable'];

    protected $casts = ['is_taxable' => 'boolean'];
}
