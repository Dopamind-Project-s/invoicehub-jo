<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table): void {
                if (Schema::hasColumn('companies', 'jofotara_client_id')) {
                    $table->text('jofotara_client_id')->nullable()->change();
                }
                if (Schema::hasColumn('companies', 'jofotara_secret_key')) {
                    $table->longText('jofotara_secret_key')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'company_id')) {
            $this->normalizeNullableCompanyIds('roles');
        }

        foreach (['model_has_roles', 'model_has_permissions'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                DB::table($table)->whereNull('company_id')->delete();
            }
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        // Non-destructive stabilization migration; do not shrink encrypted credential columns or delete permission data.
    }

    private function normalizeNullableCompanyIds(string $table): void
    {
        DB::table($table)->where('company_id', 0)->update(['company_id' => null]);
    }
};
