<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY invoice_type VARCHAR(50) NOT NULL DEFAULT 'tax_invoice'");
            DB::statement("ALTER TABLE invoices MODIFY invoice_subtype VARCHAR(50) NOT NULL DEFAULT 'SALE'");
            DB::statement("ALTER TABLE invoices MODIFY invoice_scope VARCHAR(50) NOT NULL DEFAULT 'local'");
            DB::statement("ALTER TABLE invoices MODIFY payment_type VARCHAR(50) NOT NULL DEFAULT 'receivable'");
            DB::statement("ALTER TABLE invoices MODIFY taxpayer_type VARCHAR(50) NOT NULL DEFAULT 'income'");
            DB::statement("ALTER TABLE invoices MODIFY status VARCHAR(50) NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: keeping VARCHAR columns preserves both MVP and legacy JoFotara values.
    }
};
