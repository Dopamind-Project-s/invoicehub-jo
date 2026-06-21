<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices') || ! Schema::hasColumn('invoices', 'icv')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE invoices DROP INDEX invoices_icv_unique');
            } catch (Throwable) {
                // Index may have been removed already on upgraded installations.
            }

            try {
                DB::statement('CREATE INDEX invoices_supplier_icv_index ON invoices (supplier_id, icv)');
            } catch (Throwable) {
                // Non-unique helper index may already exist.
            }
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS invoices_icv_unique');
            DB::statement('CREATE INDEX IF NOT EXISTS invoices_supplier_icv_index ON invoices (supplier_id, icv)');
            return;
        }

        try {
            Schema::table('invoices', fn ($table) => $table->dropUnique('invoices_icv_unique'));
        } catch (Throwable) {
            // Keep migration non-destructive for databases that already relaxed this index.
        }
    }

    public function down(): void
    {
        // Intentionally do not restore the global unique ICV constraint: local invoices and
        // JoFotara invoices now have separate sequencing responsibilities.
    }
};
