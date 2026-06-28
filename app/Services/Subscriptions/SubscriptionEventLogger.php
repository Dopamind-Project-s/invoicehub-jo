<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionEvent;
use App\Models\User;

class SubscriptionEventLogger
{
    public function record(Company $company, ?Subscription $subscription, string $type, string $source = 'system', ?User $actor = null, array $payload = []): SubscriptionEvent
    {
        return SubscriptionEvent::updateOrCreate(
            [
                'company_id' => $company->id,
                'subscription_id' => $subscription?->id,
                'event_type' => $type,
                'occurred_at' => $payload['occurred_at'] ?? now(),
            ],
            [
                'source' => $source,
                'actor_id' => $actor?->id,
                'payload' => $payload === [] ? null : $payload,
            ]
        );
    }
}
