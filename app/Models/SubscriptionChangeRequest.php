<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionChangeRequest extends Model
{
    protected $fillable = ['company_id', 'current_subscription_id', 'requested_plan_id', 'request_type', 'billing_cycle', 'requested_effective_date', 'status', 'notes', 'requested_by', 'reviewed_by', 'reviewed_at'];
    protected $casts = ['requested_effective_date' => 'datetime', 'reviewed_at' => 'datetime'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function currentSubscription(): BelongsTo { return $this->belongsTo(Subscription::class, 'current_subscription_id'); }
    public function requestedPlan(): BelongsTo { return $this->belongsTo(Plan::class, 'requested_plan_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
}
