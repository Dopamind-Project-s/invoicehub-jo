<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;
use InvalidArgumentException;

class InvoiceTypeCodeService
{
    private const MAP = [
        'income:cash:local' => '011',
        'income:receivable:local' => '021',
        'income:cash:export' => '111',
        'income:receivable:export' => '121',
        'general_sales:cash:local' => '012',
        'general_sales:receivable:local' => '022',
        'general_sales:cash:export' => '112',
        'general_sales:receivable:export' => '122',
        'general_sales:cash:development_area' => '212',
        'general_sales:receivable:development_area' => '222',
        'special_sales:cash:local' => '013',
        'special_sales:receivable:local' => '023',
        'special_sales:cash:export' => '113',
        'special_sales:receivable:export' => '123',
        'special_sales:cash:development_area' => '213',
        'special_sales:receivable:development_area' => '223',
    ];

    public function nameFor(Invoice $invoice): string
    {
        $key = ($invoice->taxpayer_type ?: 'general_sales').':'.($invoice->payment_type ?: 'receivable').':'.($invoice->invoice_scope ?: 'local');

        return self::MAP[$key] ?? throw new InvalidArgumentException("Unsupported JoFotara invoice type code combination [$key].");
    }
}
