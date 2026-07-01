<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Transition subscriptions to grace or expired based on period and grace dates.';

    public function handle(): int
    {
        $graced = 0;
        $expired = 0;

        Subscription::query()
            ->whereIn('status', ['active', 'trial', 'trialing', 'grace'])
            ->whereNotNull('current_period_end_at')
            ->where('current_period_end_at', '<', now())
            ->orderBy('id')
            ->chunkById(100, function ($subscriptions) use (&$graced, &$expired): void {
                foreach ($subscriptions as $subscription) {
                    if ($subscription->grace_ends_at && now()->lessThanOrEqualTo($subscription->grace_ends_at)) {
                        if ($subscription->status !== 'grace') {
                            $subscription->forceFill(['status' => 'grace', 'status_reason' => 'period_ended_in_grace'])->save();
                            $graced++;
                        }

                        continue;
                    }

                    $subscription->forceFill(['status' => 'expired', 'status_reason' => 'grace_ended', 'ended_at' => $subscription->ended_at ?: now()])->save();
                    $expired++;
                }
            });

        $this->info("Subscriptions moved to grace: {$graced}");
        $this->info("Subscriptions expired: {$expired}");

        return self::SUCCESS;
    }
}
