<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetLocalJofotaraAttempts extends Command
{
    protected $signature = 'jofotara:reset-local-attempts';

    protected $description = 'Remove local draft/error/rejected demo invoices without deleting accepted invoices.';

    public function handle(): int
    {
        $deleted = DB::transaction(function (): int {
            $company = Company::where('tax_number', '9578331')->first();
            $query = Invoice::query()->whereIn('status', ['DRAFT', 'ERROR', 'REJECTED']);
            if ($company) {
                $query->where('supplier_id', $company->id);
            }
            $ids = $query->pluck('id');
            Invoice::whereKey($ids)->delete();
            if ($company) {
                $lastAcceptedIcv = (int) Invoice::where('supplier_id', $company->id)->where('status', 'ACCEPTED')->max('icv');
                $company->forceFill(['last_icv' => $lastAcceptedIcv])->save();
            }
            if (Invoice::query()->count() === 0) {
                $this->resetInvoiceAutoIncrement();
            }

            return $ids->count();
        });

        $this->line('deleted local draft/error/rejected invoices: '.$deleted);
        $this->line('accepted invoices were not deleted.');

        return self::SUCCESS;
    }

    private function resetInvoiceAutoIncrement(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'invoices'");
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE invoices AUTO_INCREMENT = 1');
        }
    }
}
