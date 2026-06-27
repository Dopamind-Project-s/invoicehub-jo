<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\Subscriptions\SubscriptionAccessService;

class Company extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'legal_name_ar', 'legal_name_en', 'trade_name', 'tax_number', 'national_number', 'registration_number', 'branch_code', 'country_code', 'city', 'street', 'building_no', 'postal_code', 'email', 'phone', 'status', 'logo_path', 'default_language', 'economic_activity', 'default_currency', 'icv_prefix', 'jofotara_client_id', 'jofotara_secret_key', 'jofotara_source_id', 'last_icv', 'is_active'];

    protected $hidden = ['jofotara_client_id', 'jofotara_secret_key'];

    protected $casts = ['is_active' => 'boolean', 'last_icv' => 'integer'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'supplier_id');
    }

    public function featureKeys(): BelongsToMany
    {
        return $this->belongsToMany(FeatureKey::class, 'company_feature_keys')->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(CompanySetting::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trial', 'trialing', 'grace'])
            ->where(function ($query): void {
                $query->whereNull('current_period_start_at')->orWhere('current_period_start_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('current_period_end_at')
                    ->orWhere('current_period_end_at', '>=', now())
                    ->orWhere('grace_ends_at', '>=', now());
            })
            ->latestOfMany();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' || ! $this->is_active;
    }

    /** @return array<string,mixed> */
    public function subscriptionAccess(): array
    {
        return app(SubscriptionAccessService::class)->resolve($this);
    }

    public function effectiveSubscriptionStatus(): string
    {
        return (string) $this->subscriptionAccess()['effective_status'];
    }

    public function hasActiveSubscription(): bool
    {
        return in_array($this->effectiveSubscriptionStatus(), ['active', 'trialing'], true);
    }

    public function isInGracePeriod(): bool
    {
        return $this->effectiveSubscriptionStatus() === 'grace';
    }

    public function isSubscriptionExpired(): bool
    {
        return $this->effectiveSubscriptionStatus() === 'expired';
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
