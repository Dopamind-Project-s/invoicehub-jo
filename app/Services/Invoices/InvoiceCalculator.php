<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Collection;

class InvoiceCalculator
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @return array{subtotal:string,discount_total:string,tax_total:string,grand_total:string,items:array<int,array<string,string|null>>}
     */
    public function calculate(array $items): array
    {
        $lines = collect($items)->map(fn (array $item): array => $this->line($item));

        return [
            'subtotal' => $this->money($lines->sum(fn (array $line): float => (float) $line['subtotal'])),
            'discount_total' => $this->money($lines->sum(fn (array $line): float => (float) $line['discount_amount'])),
            'tax_total' => $this->money($lines->sum(fn (array $line): float => (float) $line['tax_amount'])),
            'grand_total' => $this->money($lines->sum(fn (array $line): float => (float) $line['line_total'])),
            'items' => $lines->all(),
        ];
    }

    /** @param array<string, mixed> $item */
    public function line(array $item): array
    {
        $quantity = (float) ($item['quantity'] ?? 0);
        $unitPrice = (float) ($item['unit_price'] ?? 0);
        $discount = (float) ($item['discount_amount'] ?? $item['discount'] ?? 0);
        $taxPercent = (float) ($item['tax_percent'] ?? 0);
        $subtotal = $quantity * $unitPrice;
        $taxable = max($subtotal - $discount, 0);
        $taxAmount = $taxable * ($taxPercent / 100);

        return [
            'product_id' => isset($item['product_id']) && $item['product_id'] !== '' ? (string) $item['product_id'] : null,
            'description' => (string) ($item['description'] ?? ''),
            'quantity' => $this->money($quantity),
            'unit_price' => $this->money($unitPrice),
            'subtotal' => $this->money($subtotal),
            'discount_amount' => $this->money($discount),
            'discount' => $this->money($discount),
            'line_extension_amount' => $this->money($taxable),
            'tax_percent' => $this->money($taxPercent),
            'tax_amount' => $this->money($taxAmount),
            'line_total' => $this->money($taxable + $taxAmount),
            'tax_category' => $taxPercent > 0 ? 'S' : 'Z',
        ];
    }

    /** @param array<int, array<string, mixed>> $items */
    public function recalculate(Invoice $invoice, array $items): Invoice
    {
        $totals = $this->calculate($items);
        $invoice->forceFill([
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount_total'],
            'discount_total' => $totals['discount_total'],
            'tax_amount' => $totals['tax_total'],
            'tax_total' => $totals['tax_total'],
            'taxable_amount' => $this->money((float) $totals['subtotal'] - (float) $totals['discount_total']),
            'total_amount' => $totals['grand_total'],
            'payable_amount' => $totals['grand_total'],
            'grand_total' => $totals['grand_total'],
        ]);

        return $invoice;
    }

    private function money(float $value): string
    {
        return number_format($value, 6, '.', '');
    }
}
