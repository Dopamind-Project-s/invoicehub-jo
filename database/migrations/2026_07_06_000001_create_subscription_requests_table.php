<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('company_name');
            $table->string('applicant_name');
            $table->string('email')->index();
            $table->string('phone', 50)->index();
            $table->string('whatsapp', 50)->nullable();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'contacted', 'approved', 'provisioned', 'rejected'])->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->timestamp('provisioned_at')->nullable();
            $table->foreignId('provisioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['plan_id', 'billing_cycle']);
            $table->index(['billing_cycle', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_requests');
    }
};
