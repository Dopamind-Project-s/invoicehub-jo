<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'payment_status')) {
                $table->string('payment_status', 30)->nullable()->after('payment_reference')->index();
            }
            if (! Schema::hasColumn('subscriptions', 'renewal_source')) {
                $table->string('renewal_source', 50)->nullable()->after('payment_status')->index();
            }
            if (! Schema::hasColumn('subscriptions', 'renewed_by')) {
                $table->foreignId('renewed_by')->nullable()->after('renewal_source')->constrained('users')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('subscription_events')) {
            Schema::create('subscription_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event_type', 80)->index();
                $table->string('source', 50)->default('system')->index();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('payload')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();
                $table->index(['company_id', 'occurred_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_events');
        Schema::table('subscriptions', function (Blueprint $table): void {
            if (Schema::hasColumn('subscriptions', 'renewed_by')) {
                $table->dropConstrainedForeignId('renewed_by');
            }
            foreach (['renewal_source', 'payment_status'] as $column) {
                if (Schema::hasColumn('subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
