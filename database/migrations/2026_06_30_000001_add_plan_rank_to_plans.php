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
        Schema::table('plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('plans', 'plan_rank')) {
                $table->unsignedInteger('plan_rank')->default(0)->index()->after('sort_order');
            }
        });

        DB::table('plans')->where('plan_rank', 0)->update(['plan_rank' => DB::raw('sort_order')]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            if (Schema::hasColumn('plans', 'plan_rank')) {
                $table->dropColumn('plan_rank');
            }
        });
    }
};
