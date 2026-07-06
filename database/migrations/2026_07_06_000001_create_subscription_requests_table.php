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
            $table->string('email');
            $table->string('phone', 50);
            $table->string('whatsapp', 50)->nullable();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'contacted', 'approved', 'rejected'])->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['plan_id', 'billing_cycle']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_requests');
    }
};
