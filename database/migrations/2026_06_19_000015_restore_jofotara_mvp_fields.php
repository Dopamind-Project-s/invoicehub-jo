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
        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'source')) {
                $table->string('source')->default('local')->after('status')->index();
            }
            if (! Schema::hasColumn('invoices', 'jofotara_status')) {
                $table->string('jofotara_status')->nullable()->after('source')->index();
            }
            if (! Schema::hasColumn('invoices', 'jofotara_uuid')) {
                $table->string('jofotara_uuid')->nullable()->after('jofotara_status')->index();
            }
            if (! Schema::hasColumn('invoices', 'jofotara_qr')) {
                $table->longText('jofotara_qr')->nullable()->after('jofotara_uuid');
            }
            if (! Schema::hasColumn('invoices', 'jofotara_response')) {
                $table->longText('jofotara_response')->nullable()->after('jofotara_qr');
            }
            if (! Schema::hasColumn('invoices', 'jofotara_submitted_at')) {
                $table->timestamp('jofotara_submitted_at')->nullable()->after('jofotara_response');
            }
            if (! Schema::hasColumn('invoices', 'jofotara_error_message')) {
                $table->text('jofotara_error_message')->nullable()->after('jofotara_submitted_at');
            }
        });

        DB::table('invoices')->whereNull('source')->update(['source' => 'local']);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            foreach (['source', 'jofotara_status', 'jofotara_uuid', 'jofotara_qr', 'jofotara_response', 'jofotara_submitted_at', 'jofotara_error_message'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
