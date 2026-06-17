<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('payment_type', ['cash', 'receivable'])->nullable()->default('receivable')->after('payment_reference');
            $table->enum('taxpayer_type', ['income', 'general_sales', 'special_sales'])->nullable()->default('general_sales')->after('payment_type');
            $table->unsignedBigInteger('icv_counter')->nullable()->after('taxpayer_type');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'taxpayer_type', 'icv_counter']);
        });
    }
};
