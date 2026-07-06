<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('invoice_number');
            $table->unsignedBigInteger('icv');
            $table->string('invoice_type', 50)->default('tax_invoice');
            $table->string('invoice_subtype', 50)->default('SALE');
            $table->string('invoice_scope', 50)->default('local');
            $table->string('payment_type', 50)->default('receivable');
            $table->string('taxpayer_type', 50)->default('income');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->time('issue_time');
            $table->text('notes')->nullable();
            $table->string('currency_code', 3)->default('JOD');
            $table->string('currency', 3)->default('JOD');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->foreignId('supplier_id')->constrained('companies');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->decimal('subtotal', 18, 6)->default(0);
            $table->decimal('discount_amount', 18, 6)->default(0);
            $table->decimal('discount_total', 18, 6)->default(0);
            $table->decimal('taxable_amount', 18, 6)->default(0);
            $table->decimal('tax_amount', 18, 6)->default(0);
            $table->decimal('tax_total', 18, 6)->default(0);
            $table->decimal('total_amount', 18, 6)->default(0);
            $table->decimal('grand_total', 18, 6)->default(0);
            $table->decimal('rounding_amount', 18, 6)->default(0);
            $table->decimal('payable_amount', 18, 6)->default(0);
            $table->string('previous_invoice_hash')->nullable();
            $table->string('xml_hash')->nullable();
            $table->longText('qr_code')->nullable();
            $table->string('status', 50)->default('draft')->index();
            $table->string('source')->default('local')->index();
            $table->string('jofotara_status')->nullable()->index();
            $table->string('jofotara_validation_result')->nullable()->index();
            $table->string('jofotara_uuid')->nullable()->index();
            $table->longText('jofotara_qr')->nullable();
            $table->longText('jofotara_response')->nullable();
            $table->timestamp('jofotara_submitted_at')->nullable();
            $table->text('jofotara_error_message')->nullable();
            $table->uuid('submission_uuid')->nullable();
            $table->longText('submission_response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['supplier_id', 'icv']);
            $table->index(['company_id', 'issue_date']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'jofotara_status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
