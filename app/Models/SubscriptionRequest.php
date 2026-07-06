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
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = ['company_name', 'applicant_name', 'email', 'phone', 'whatsapp', 'plan_id', 'billing_cycle', 'notes', 'status', 'admin_notes', 'approved_at', 'approved_by'];

    protected $casts = ['approved_at' => 'datetime'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_CONTACTED, self::STATUS_APPROVED, self::STATUS_REJECTED];
    }
}
