<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY status VARCHAR(50) NOT NULL DEFAULT 'trial'");
        }

        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'billing_cycle')) {
                $table->string('billing_cycle', 30)->default('manual')->after('status')->index();
            }
            if (! Schema::hasColumn('subscriptions', 'current_period_start_at')) {
                $table->timestamp('current_period_start_at')->nullable()->after('billing_cycle');
            }
            if (! Schema::hasColumn('subscriptions', 'current_period_end_at')) {
                $table->timestamp('current_period_end_at')->nullable()->after('current_period_start_at')->index();
            }
            if (! Schema::hasColumn('subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('current_period_end_at');
            }
            if (! Schema::hasColumn('subscriptions', 'grace_ends_at')) {
                $table->timestamp('grace_ends_at')->nullable()->after('trial_ends_at')->index();
            }
            if (! Schema::hasColumn('subscriptions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('grace_ends_at');
            }
            if (! Schema::hasColumn('subscriptions', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('cancelled_at');
            }
            if (! Schema::hasColumn('subscriptions', 'renewed_at')) {
                $table->timestamp('renewed_at')->nullable()->after('ended_at');
            }
            if (! Schema::hasColumn('subscriptions', 'status_reason')) {
                $table->string('status_reason')->nullable()->after('renewed_at');
            }
            if (! Schema::hasColumn('subscriptions', 'source')) {
                $table->string('source')->default('admin')->after('status_reason')->index();
            }
            if (! Schema::hasColumn('subscriptions', 'price_amount')) {
                $table->decimal('price_amount', 12, 3)->nullable()->after('source');
            }
            if (! Schema::hasColumn('subscriptions', 'currency')) {
                $table->string('currency', 3)->default('JOD')->after('price_amount');
            }
            if (! Schema::hasColumn('subscriptions', 'auto_renew')) {
                $table->boolean('auto_renew')->default(false)->after('currency');
            }
            if (! Schema::hasColumn('subscriptions', 'metadata')) {
                $table->json('metadata')->nullable()->after('auto_renew');
            }
        });

        DB::table('subscriptions')->orderBy('id')->lazyById()->each(function (object $subscription): void {
            $start = $subscription->starts_at ?: $subscription->created_at ?: now();
            $end = $subscription->expires_at ?: Carbon::parse($start)->addYear();

            DB::table('subscriptions')->where('id', $subscription->id)->update([
                'billing_cycle' => $subscription->billing_cycle ?? 'manual',
                'current_period_start_at' => $subscription->current_period_start_at ?: $start,
                'current_period_end_at' => $subscription->current_period_end_at ?: $end,
                'expires_at' => $subscription->expires_at ?: $end,
                'grace_ends_at' => $subscription->grace_ends_at ?: Carbon::parse($end)->addDays(7),
                'currency' => $subscription->currency ?? 'JOD',
                'source' => $subscription->source ?? 'admin',
                'auto_renew' => $subscription->auto_renew ?? false,
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY status VARCHAR(50) NOT NULL DEFAULT 'trial'");
        }

        Schema::table('subscriptions', function (Blueprint $table): void {
            foreach (['metadata', 'auto_renew', 'currency', 'price_amount', 'source', 'status_reason', 'renewed_at', 'ended_at', 'cancelled_at', 'grace_ends_at', 'trial_ends_at', 'current_period_end_at', 'current_period_start_at', 'billing_cycle'] as $column) {
                if (Schema::hasColumn('subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
