<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use Illuminate\Support\Collection;
class InvoiceTemplateData
{
    public function __construct(
        public Invoice $invoice,
        public Company $company,
        public ?Contact $customer,
        public Collection $items,
        public array $totals,
        public array $branding,
        public array $qr,
        public array $jofotara,
        public InvoiceTemplate $template,
        public string $language,
        public string $direction,
    ) {}
}
