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
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 3);
            $table->decimal('tax_total', 12, 3)->default(0);
            $table->decimal('discount_total', 12, 3)->default(0);
            $table->decimal('total', 12, 3);
            $table->string('payment_reference')->nullable();
            $table->enum('status', ['draft', 'submitted', 'accepted', 'rejected'])->default('draft');
            $table->string('jofotara_uuid')->nullable();
            $table->longText('jofotara_qr')->nullable();
            $table->longText('jofotara_response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
