<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('plans', 'trial_days')) {
                $table->unsignedSmallInteger('trial_days')->default(0)->after('yearly_price');
            }
            if (! Schema::hasColumn('plans', 'grace_period_days')) {
                $table->unsignedSmallInteger('grace_period_days')->default(7)->after('trial_days');
            }
            if (! Schema::hasColumn('plans', 'currency')) {
                $table->string('currency', 3)->default('JOD')->after('grace_period_days');
            }
            if (! Schema::hasColumn('plans', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('currency')->index();
            }
            if (! Schema::hasColumn('plans', 'is_legacy')) {
                $table->boolean('is_legacy')->default(false)->after('is_public')->index();
            }
            if (! Schema::hasColumn('plans', 'limits')) {
                $table->json('limits')->nullable()->after('is_legacy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            foreach (['limits', 'is_legacy', 'is_public', 'currency', 'grace_period_days', 'trial_days'] as $column) {
                if (Schema::hasColumn('plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
