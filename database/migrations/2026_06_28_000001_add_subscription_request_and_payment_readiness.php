<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_change_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('requested_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('request_type', 30)->index();
            $table->string('billing_cycle', 30)->nullable()->index();
            $table->timestamp('requested_effective_date')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status']);
            $table->index(['requested_plan_id', 'billing_cycle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_change_requests');
    }
};
