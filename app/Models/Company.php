<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Company extends Model
{
    protected $fillable = ['legal_name_ar', 'legal_name_en', 'trade_name', 'tax_number', 'national_number', 'registration_number', 'branch_code', 'country_code', 'city', 'street', 'building_no', 'postal_code', 'email', 'phone', 'economic_activity', 'default_currency', 'icv_prefix', 'jofotara_client_id', 'jofotara_secret_key', 'jofotara_source_id', 'last_icv', 'is_active'];

    protected $hidden = ['jofotara_client_id', 'jofotara_secret_key'];

    protected $casts = ['is_active' => 'boolean', 'last_icv' => 'integer'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'supplier_id');
    }

    public function getJofotaraClientIdAttribute(?string $value): ?string
    {
        return $this->decryptCredential($value);
    }

    public function setJofotaraClientIdAttribute(?string $value): void
    {
        $this->attributes['jofotara_client_id'] = filled($value) ? Crypt::encryptString((string) $value) : null;
    }

    public function getJofotaraSecretKeyAttribute(?string $value): ?string
    {
        return $this->decryptCredential($value);
    }

    public function setJofotaraSecretKeyAttribute(?string $value): void
    {
        $this->attributes['jofotara_secret_key'] = filled($value) ? Crypt::encryptString((string) $value) : null;
    }

    public function hasJofotaraClientId(): bool
    {
        return filled($this->jofotara_client_id);
    }

    public function hasJofotaraSecretKey(): bool
    {
        return filled($this->jofotara_secret_key);
    }

    private function decryptCredential(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }
}
