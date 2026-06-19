<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Console\Command;

class RepairJofotaraPih extends Command
{
    protected $signature = 'jofotara:repair-pih {--company_id=}';

    protected $description = 'Recalculate and fill previous_invoice_hash values in ICV order.';

    public function handle(): int
    {
        $companies = Company::query()
            ->when($this->option('company_id'), fn ($query, $id) => $query->whereKey($id))
            ->get();
        $failed = false;

        foreach ($companies as $company) {
            $previousHash = null;
            $invoices = Invoice::query()->where('supplier_id', $company->id)->orderBy('icv')->get();
            foreach ($invoices as $invoice) {
                if ((int) $invoice->icv === 1) {
                    $previousHash = (string) config('services.jofotara.initial_pih', '');
                }

                if (blank($previousHash)) {
                    $this->error("invoice {$invoice->invoice_number}: cannot fill PIH; previous hash is missing.");
                    $failed = true;
                } else {
                    $invoice->forceFill(['previous_invoice_hash' => $previousHash])->save();
                    $this->line("invoice {$invoice->invoice_number}: PIH filled.");
                }

                if (blank($invoice->xml_hash)) {
                    $this->warn("invoice {$invoice->invoice_number}: xml_hash missing; next invoice cannot use it as PIH until prepared/generated.");
                    $previousHash = null;
                    $failed = true;
                } else {
                    $previousHash = (string) $invoice->xml_hash;
                }
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
