<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('id');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('email')->index();
            $table->string('logo_path')->nullable()->after('status');
            $table->string('default_language', 5)->default('ar')->after('logo_path');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('user')->after('password')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'status', 'logo_path', 'default_language']);
        });
    }
};
