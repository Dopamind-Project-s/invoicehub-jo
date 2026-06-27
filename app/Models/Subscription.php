<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['company_id', 'plan_id', 'starts_at', 'expires_at', 'status', 'billing_cycle', 'current_period_start_at', 'current_period_end_at', 'trial_ends_at', 'grace_ends_at', 'cancelled_at', 'ended_at', 'renewed_at', 'status_reason', 'source', 'price_amount', 'currency', 'auto_renew', 'metadata'];

    protected $casts = ['starts_at' => 'datetime', 'expires_at' => 'datetime', 'current_period_start_at' => 'datetime', 'current_period_end_at' => 'datetime', 'trial_ends_at' => 'datetime', 'grace_ends_at' => 'datetime', 'cancelled_at' => 'datetime', 'ended_at' => 'datetime', 'renewed_at' => 'datetime', 'price_amount' => 'decimal:3', 'auto_renew' => 'boolean', 'metadata' => 'array'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
