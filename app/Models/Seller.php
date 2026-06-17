<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $fillable = ['name', 'tax_number', 'national_number', 'income_source_sequence', 'phone', 'email', 'address', 'logo_path', 'jofotara_client_id', 'jofotara_secret_key', 'jofotara_source_id', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
