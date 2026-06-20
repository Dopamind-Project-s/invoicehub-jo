<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->date('due_date')->nullable()->after('issue_date');
            $table->text('notes')->nullable()->after('due_date');
            $table->decimal('tax_total', 18, 6)->default(0)->after('tax_amount');
            $table->decimal('discount_total', 18, 6)->default(0)->after('discount_amount');
            $table->decimal('grand_total', 18, 6)->default(0)->after('total_amount');
            $table->string('currency', 3)->default('JOD')->after('currency_code');
            $table->foreignId('created_by')->nullable()->after('currency')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'issue_date']);
        });

        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->decimal('discount_amount', 18, 6)->default(0)->after('discount');
        });

        DB::table('invoices')->whereNull('company_id')->update(['company_id' => DB::raw('supplier_id')]);
        DB::table('invoices')->whereNull('currency')->update(['currency' => DB::raw('currency_code')]);
        DB::table('invoices')->where('tax_total', 0)->update(['tax_total' => DB::raw('tax_amount')]);
        DB::table('invoices')->where('discount_total', 0)->update(['discount_total' => DB::raw('discount_amount')]);
        DB::table('invoices')->where('grand_total', 0)->update(['grand_total' => DB::raw('payable_amount')]);
        DB::table('invoice_items')->where('discount_amount', 0)->update(['discount_amount' => DB::raw('discount')]);
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->dropColumn('discount_amount');
        });
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['company_id', 'issue_date']);
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('contact_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['due_date', 'notes', 'tax_total', 'discount_total', 'grand_total', 'currency', 'approved_at']);
        });
    }
};
