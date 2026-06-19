<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->decimal('quantity', 18, 6);
            $table->decimal('unit_price', 18, 6);
            $table->decimal('discount', 18, 6)->default(0);
            $table->decimal('line_extension_amount', 18, 6)->default(0);
            $table->string('tax_category');
            $table->decimal('tax_percent', 9, 6)->default(0);
            $table->decimal('tax_amount', 18, 6)->default(0);
            $table->decimal('line_total', 18, 6)->default(0);
            $table->timestamps();
        });
        Schema::create('invoice_xml_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->longText('generated_xml');
            $table->longText('canonical_xml')->nullable();
            $table->string('hash')->nullable();
            $table->json('validation_result')->nullable();
            $table->json('submission_result')->nullable();
            $table->longText('raw_response')->nullable();
            $table->timestamps();
        });
        Schema::create('invoice_submission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->uuid('submission_uuid')->nullable();
            $table->string('status');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('request_payload')->nullable();
            $table->longText('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempt')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_submission_logs');
        Schema::dropIfExists('invoice_xml_logs');
        Schema::dropIfExists('invoice_items');
    }
};
