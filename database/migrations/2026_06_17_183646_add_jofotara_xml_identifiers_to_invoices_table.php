<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('jofotara_invoice_number')->nullable()->unique()->after('invoice_number');
            $table->uuid('jofotara_xml_uuid')->nullable()->after('jofotara_invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['jofotara_invoice_number', 'jofotara_xml_uuid']);
        });
    }
};
