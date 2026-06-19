<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('role_has_permissions', 'company_id')) {
            $rows = DB::table('role_has_permissions')
                ->select('permission_id', 'role_id')
                ->distinct()
                ->get();

            Schema::drop('role_has_permissions');

            Schema::create('role_has_permissions', function (Blueprint $table): void {
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });

            foreach ($rows as $row) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $row->permission_id,
                    'role_id' => $row->role_id,
                ]);
            }
        }

        if (! Schema::hasTable('permission_cache')) {
            Schema::create('permission_cache', function (Blueprint $table): void {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('role_has_permissions', 'company_id')) {
            Schema::table('role_has_permissions', function (Blueprint $table): void {
                $table->foreignId('company_id')->nullable()->after('role_id')->constrained()->cascadeOnDelete();
            });
        }
    }
};
