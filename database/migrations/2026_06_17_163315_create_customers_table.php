<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->timestamps();
        });
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->timestamps();
        });
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->timestamps();
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->string('code', 3)->primary();
            $table->string('name');
            $table->unsignedTinyInteger('minor_units')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('tax_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('tax_rate', 9, 6)->default(0);
            $table->string('tax_code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('invoice_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name_ar');
            $table->string('legal_name_en')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('tax_number')->unique();
            $table->string('national_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('branch_code')->default('0');
            $table->string('country_code', 2)->default('JO');
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('building_no')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('economic_activity')->nullable();
            $table->string('default_currency', 3)->default('JOD');
            $table->string('icv_prefix')->default('INV');
            $table->string('jofotara_client_id')->nullable();
            $table->string('jofotara_secret_key')->nullable();
            $table->string('jofotara_source_id')->nullable();
            $table->unsignedBigInteger('last_icv')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->string('item_code')->unique();
            $table->string('barcode')->nullable();
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('tax_category_id')->constrained();
            $table->decimal('default_price', 18, 6)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->enum('customer_type', ['INDIVIDUAL', 'BUSINESS', 'GOVERNMENT'])->default('INDIVIDUAL');
            $table->string('name');
            $table->string('tax_number')->nullable();
            $table->string('national_number')->nullable();
            $table->string('country_code', 2)->default('JO');
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('invoice_statuses');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('tax_categories');
        Schema::dropIfExists('units');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('governorates');
        Schema::dropIfExists('countries');
    }
};
