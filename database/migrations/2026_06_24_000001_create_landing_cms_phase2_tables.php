<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('landing_hero_slides', function (Blueprint $table): void {
            $table->id(); $table->string('title_ar'); $table->string('title_en')->nullable(); $table->text('subtitle_ar')->nullable(); $table->text('subtitle_en')->nullable();
            $table->string('primary_cta_text_ar')->nullable(); $table->string('primary_cta_text_en')->nullable(); $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_text_ar')->nullable(); $table->string('secondary_cta_text_en')->nullable(); $table->string('secondary_cta_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('landing_testimonials', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('establishment')->nullable(); $table->string('position')->nullable(); $table->text('testimonial_ar'); $table->text('testimonial_en')->nullable(); $table->unsignedTinyInteger('rating')->default(5); $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('landing_integrations', function (Blueprint $table): void {
            $table->id(); $table->string('name_ar'); $table->string('name_en')->nullable(); $table->text('description_ar')->nullable(); $table->text('description_en')->nullable(); $table->string('icon')->nullable(); $table->string('status')->default('available'); $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('landing_statistics', function (Blueprint $table): void {
            $table->id(); $table->string('label_ar'); $table->string('label_en')->nullable(); $table->string('value'); $table->string('suffix')->nullable(); $table->string('icon')->nullable(); $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('landing_partners', function (Blueprint $table): void {
            $table->id(); $table->string('name_ar'); $table->string('name_en')->nullable(); $table->string('url')->nullable(); $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('landing_events', function (Blueprint $table): void {
            $table->id(); $table->string('event_type', 40)->index(); $table->text('url')->nullable(); $table->text('referrer')->nullable(); $table->text('user_agent')->nullable(); $table->string('ip_hash', 64)->nullable()->index(); $table->json('metadata')->nullable(); $table->timestamp('created_at')->useCurrent();
        });
        if (! Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table): void {
                $table->id(); $table->morphs('model'); $table->uuid()->nullable()->unique(); $table->string('collection_name'); $table->string('name'); $table->string('file_name'); $table->string('mime_type')->nullable(); $table->string('disk'); $table->string('conversions_disk')->nullable(); $table->unsignedBigInteger('size'); $table->json('manipulations'); $table->json('custom_properties'); $table->json('generated_conversions'); $table->json('responsive_images'); $table->unsignedInteger('order_column')->nullable()->index(); $table->nullableTimestamps();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('landing_events'); Schema::dropIfExists('landing_partners'); Schema::dropIfExists('landing_statistics'); Schema::dropIfExists('landing_integrations'); Schema::dropIfExists('landing_testimonials'); Schema::dropIfExists('landing_hero_slides'); }
};
