<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoice_templates', 'preview_image')) {
                $table->string('preview_image')->nullable()->after('layout_type');
            }
            if (! Schema::hasColumn('invoice_templates', 'view_path')) {
                $table->string('view_path')->nullable()->after('preview_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_templates', function (Blueprint $table): void {
            if (Schema::hasColumn('invoice_templates', 'view_path')) {
                $table->dropColumn('view_path');
            }
            if (Schema::hasColumn('invoice_templates', 'preview_image')) {
                $table->dropColumn('preview_image');
            }
        });
    }
};
