<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscription_requests MODIFY status ENUM('pending','contacted','approved','provisioned','rejected') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('subscription_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscription_requests', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('approved_by')->constrained('companies')->nullOnDelete();
            }
            if (! Schema::hasColumn('subscription_requests', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('company_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('subscription_requests', 'subscription_id')) {
                $table->foreignId('subscription_id')->nullable()->after('user_id')->constrained('subscriptions')->nullOnDelete();
            }
            if (! Schema::hasColumn('subscription_requests', 'provisioned_at')) {
                $table->timestamp('provisioned_at')->nullable()->after('subscription_id');
            }
            if (! Schema::hasColumn('subscription_requests', 'provisioned_by')) {
                $table->foreignId('provisioned_by')->nullable()->after('provisioned_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table): void {
            foreach (['provisioned_by', 'subscription_id', 'user_id', 'company_id'] as $column) {
                if (Schema::hasColumn('subscription_requests', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            foreach (['provisioned_at'] as $column) {
                if (Schema::hasColumn('subscription_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscription_requests MODIFY status ENUM('pending','contacted','approved','rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
