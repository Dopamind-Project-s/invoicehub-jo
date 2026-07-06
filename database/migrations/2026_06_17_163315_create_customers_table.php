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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('legal_name_ar');
            $table->string('legal_name_en')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('tax_number', 50)->unique();
            $table->string('national_number')->nullable()->index();
            $table->string('registration_number')->nullable()->index();
            $table->string('branch_code')->default('0');
            $table->string('country_code', 2)->default('JO');
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('building_no')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->enum('status', ['active', 'suspended'])->default('active')->index();
            $table->string('logo_path')->nullable();
            $table->string('default_language', 5)->default('ar');
            $table->string('economic_activity')->nullable();
            $table->string('default_currency', 3)->default('JOD');
            $table->string('icv_prefix')->default('INV');
            $table->text('jofotara_client_id')->nullable();
            $table->longText('jofotara_secret_key')->nullable();
            $table->string('jofotara_source_id', 50)->nullable();
            $table->unsignedBigInteger('last_icv')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('symbol')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active']);
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

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('icon', 20)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active']);
        });

        Schema::create('tax_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('tax_type')->default('sales');
            $table->decimal('tax_percent', 9, 6)->default(0);
            $table->string('jofotara_tax_code')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['company_id', 'is_active']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('tax_category_id')->constrained();
            $table->foreignId('tax_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('product')->index();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable()->index();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('item_code')->unique();
            $table->decimal('default_price', 18, 6)->default(0);
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('cost', 18, 6)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'type']);
            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'barcode']);
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
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->timestamps();
        });
        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('customer')->index();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('tax_number')->nullable()->index();
            $table->string('national_number')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('JO');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'tax_number']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tax_profiles');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('invoice_statuses');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('tax_categories');
        Schema::dropIfExists('units');
        Schema::table('users', function (Blueprint $table): void { $table->dropForeign(['company_id']); });
        Schema::dropIfExists('companies');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('governorates');
        Schema::dropIfExists('countries');
    }
};
