<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROVISIONED = 'provisioned';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = ['company_name', 'applicant_name', 'email', 'phone', 'whatsapp', 'plan_id', 'billing_cycle', 'notes', 'status', 'admin_notes', 'approved_at', 'approved_by', 'company_id', 'user_id', 'subscription_id', 'provisioned_at', 'provisioned_by'];

    protected $casts = ['approved_at' => 'datetime', 'provisioned_at' => 'datetime'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function provisionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provisioned_by');
    }

    public function isProvisioned(): bool
    {
        return $this->status === self::STATUS_PROVISIONED || $this->company_id || $this->subscription_id;
    }

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_CONTACTED, self::STATUS_APPROVED, self::STATUS_PROVISIONED, self::STATUS_REJECTED];
    }
}
