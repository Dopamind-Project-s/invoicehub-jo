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
            $table->uuid('uuid')->unique();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('icv')->unique();
            $table->enum('invoice_type', ['STANDARD', 'SIMPLIFIED'])->default('STANDARD');
            $table->enum('invoice_subtype', ['SALE', 'RETURN', 'DEBIT_NOTE', 'CREDIT_NOTE'])->default('SALE');
            $table->date('issue_date');
            $table->time('issue_time');
            $table->string('currency_code', 3)->default('JOD');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->foreignId('supplier_id')->constrained('companies');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 18, 6)->default(0);
            $table->decimal('discount_amount', 18, 6)->default(0);
            $table->decimal('taxable_amount', 18, 6)->default(0);
            $table->decimal('tax_amount', 18, 6)->default(0);
            $table->decimal('total_amount', 18, 6)->default(0);
            $table->decimal('rounding_amount', 18, 6)->default(0);
            $table->decimal('payable_amount', 18, 6)->default(0);
            $table->string('previous_invoice_hash')->nullable();
            $table->string('xml_hash')->nullable();
            $table->longText('qr_code')->nullable();
            $table->enum('status', ['DRAFT', 'GENERATED', 'SIGNED', 'SUBMITTED', 'ACCEPTED', 'REJECTED'])->default('DRAFT');
            $table->uuid('submission_uuid')->nullable();
            $table->longText('submission_response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
