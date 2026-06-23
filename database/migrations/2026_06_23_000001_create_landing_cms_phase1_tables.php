<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group')->index();
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->string('locale', 8)->nullable()->index();
            $table->boolean('is_public')->default(true)->index();
            $table->timestamps();
            $table->unique(['group', 'key', 'locale']);
        });

        Schema::create('landing_faqs', function (Blueprint $table): void {
            $table->id();
            $table->string('question_ar');
            $table->string('question_en')->nullable();
            $table->text('answer_ar');
            $table->text('answer_en')->nullable();
            $table->string('category')->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('plans', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (! Schema::hasColumn('plans', 'name_en')) {
                $table->string('name_en')->nullable()->after('name_ar');
            }
            if (! Schema::hasColumn('plans', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
            if (! Schema::hasColumn('plans', 'description_en')) {
                $table->text('description_en')->nullable()->after('description_ar');
            }
            if (! Schema::hasColumn('plans', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->index()->after('billing_cycle');
            }
            if (! Schema::hasColumn('plans', 'is_recommended')) {
                $table->boolean('is_recommended')->default(false)->index()->after('is_active');
            }
        });

        DB::table('plans')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);
        DB::table('plans')->whereNull('description_ar')->update(['description_ar' => DB::raw('description')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_faqs');
        Schema::dropIfExists('site_settings');
    }
};
