<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_keys', function (Blueprint $table): void {
            if (! Schema::hasColumn('feature_keys', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (! Schema::hasColumn('feature_keys', 'name_en')) {
                $table->string('name_en')->nullable()->after('name_ar');
            }
            if (! Schema::hasColumn('feature_keys', 'category')) {
                $table->string('category')->nullable()->after('description')->index();
            }
        });

        Schema::table('plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('plans', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('plans', 'monthly_price')) {
                $table->decimal('monthly_price', 10, 3)->default(0)->after('price');
            }
            if (! Schema::hasColumn('plans', 'yearly_price')) {
                $table->decimal('yearly_price', 10, 3)->default(0)->after('monthly_price');
            }
        });

        if (! Schema::hasTable('feature_key_plan')) {
            Schema::create('feature_key_plan', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('feature_key_id')->constrained('feature_keys')->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['feature_key_id', 'plan_id']);
            });
        }

        DB::table('feature_keys')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);
        DB::table('feature_keys')->whereNull('name_en')->update(['name_en' => DB::raw('name')]);
        DB::table('feature_keys')->whereNull('category')->update(['category' => 'core']);
        DB::table('plans')->where('monthly_price', 0)->update(['monthly_price' => DB::raw('price')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_key_plan');
    }
};
