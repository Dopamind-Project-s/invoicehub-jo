<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('code');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active']);
        });

        Schema::table('units', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('name_ar')->nullable()->after('code');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->string('symbol')->nullable()->after('name_en');
            $table->boolean('is_active')->default(true)->after('description');
            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'code']);
        });

        Schema::create('tax_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('tax_type')->default('sales');
            $table->decimal('tax_percent', 9, 6)->default(0);
            $table->string('jofotara_tax_code')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'is_active']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('company_id')->constrained('product_categories')->nullOnDelete();
            $table->foreignId('tax_profile_id')->nullable()->after('tax_category_id')->constrained()->nullOnDelete();
            $table->string('type')->default('product')->after('tax_profile_id');
            $table->string('sku')->nullable()->after('type');
            $table->decimal('price', 18, 6)->default(0)->after('description');
            $table->decimal('cost', 18, 6)->nullable()->after('price');
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'type']);
            $table->unique(['company_id', 'sku']);
        });

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('customer');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('national_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('JO');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'is_active']);
            $table->index('tax_number');
            $table->index('national_number');
        });

        DB::table('units')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);
        DB::table('units')->whereNull('name_en')->update(['name_en' => DB::raw('name')]);
        DB::table('units')->whereNull('symbol')->update(['symbol' => DB::raw('code')]);
        DB::table('products')->whereNull('sku')->update(['sku' => DB::raw('item_code')]);
        DB::table('products')->where('price', 0)->update(['price' => DB::raw('default_price')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['company_id', 'sku']);
            $table->dropIndex(['company_id', 'is_active']);
            $table->dropIndex(['company_id', 'type']);
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('tax_profile_id');
            $table->dropColumn(['type', 'sku', 'price', 'cost']);
        });
        Schema::dropIfExists('tax_profiles');
        Schema::table('units', function (Blueprint $table): void {
            $table->dropUnique(['company_id', 'code']);
            $table->dropIndex(['company_id', 'is_active']);
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['name_ar', 'name_en', 'symbol', 'is_active']);
            $table->unique('code');
        });
        Schema::dropIfExists('product_categories');
    }
};
