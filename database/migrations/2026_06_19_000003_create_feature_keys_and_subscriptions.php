<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_keys', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('price', 12, 3)->default(0);
            $table->decimal('monthly_price', 10, 3)->default(0);
            $table->decimal('yearly_price', 10, 3)->default(0);
            $table->string('billing_cycle', 30)->default('monthly')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->unsignedInteger('plan_rank')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_recommended')->default(false)->index();
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedSmallInteger('grace_period_days')->default(7);
            $table->string('currency', 3)->default('JOD');
            $table->boolean('is_public')->default(true)->index();
            $table->boolean('is_legacy')->default(false)->index();
            $table->json('limits')->nullable();
            $table->timestamps();

            $table->index(['is_public', 'is_active', 'plan_rank']);
        });

        Schema::create('feature_key_plan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feature_key_id')->constrained('feature_keys')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['feature_key_id', 'plan_id']);
            $table->index(['plan_id', 'feature_key_id']);
        });

        Schema::create('company_feature_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_key_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'feature_key_id']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('status', 50)->default('trial')->index();
            $table->string('billing_cycle', 30)->default('manual')->index();
            $table->timestamp('current_period_start_at')->nullable();
            $table->timestamp('current_period_end_at')->nullable()->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->string('status_reason')->nullable();
            $table->string('source')->default('admin')->index();
            $table->string('payment_provider')->nullable()->index();
            $table->string('payment_reference')->nullable()->index();
            $table->string('payment_status', 30)->nullable()->index();
            $table->string('renewal_source', 50)->nullable()->index();
            $table->foreignId('renewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('price_amount', 12, 3)->nullable();
            $table->string('currency', 3)->default('JOD');
            $table->boolean('auto_renew')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['status', 'current_period_end_at']);
            $table->index(['plan_id', 'billing_cycle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('company_feature_keys');
        Schema::dropIfExists('feature_key_plan');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('feature_keys');
    }
};
