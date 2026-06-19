<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['legal_name_ar', 'legal_name_en', 'trade_name', 'tax_number', 'national_number', 'registration_number', 'branch_code', 'country_code', 'city', 'street', 'building_no', 'postal_code', 'email', 'phone', 'economic_activity', 'default_currency', 'icv_prefix', 'jofotara_client_id', 'jofotara_secret_key', 'jofotara_source_id', 'last_icv', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'last_icv' => 'integer'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'supplier_id');
    }
}
