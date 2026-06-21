<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'jofotara_validation_result')) {
                $table->string('jofotara_validation_result')->nullable()->after('jofotara_status')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'jofotara_validation_result')) {
                $table->dropColumn('jofotara_validation_result');
            }
        });
    }
};
