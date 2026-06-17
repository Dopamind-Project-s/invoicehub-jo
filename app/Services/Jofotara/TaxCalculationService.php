<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

class TaxCalculationService
{
    public function calculateLine(string $quantity, string $unitPrice, string $discount, string $taxPercent): array
    {
        $scale = 6;
        $gross = bcmul($quantity, $unitPrice, $scale);
        $net = bcsub($gross, $discount, $scale);
        if (bccomp($net, '0', $scale) < 0) {
            $net = '0.000000';
        }
        $tax = bcdiv(bcmul($net, $taxPercent, $scale), '100', $scale);
        $total = bcadd($net, $tax, $scale);

        return ['line_extension_amount' => $net, 'tax_amount' => $tax, 'line_total' => $total];
    }

    public function calculateInvoice(iterable $lines): array
    {
        $totals = ['subtotal' => '0.000000', 'discount_amount' => '0.000000', 'taxable_amount' => '0.000000', 'tax_amount' => '0.000000', 'total_amount' => '0.000000', 'rounding_amount' => '0.000000', 'payable_amount' => '0.000000'];
        foreach ($lines as $line) {
            $gross = bcmul((string) $line['quantity'], (string) $line['unit_price'], 6);
            $calc = $this->calculateLine((string) $line['quantity'], (string) $line['unit_price'], (string) ($line['discount'] ?? '0'), (string) ($line['tax_percent'] ?? '0'));
            $totals['subtotal'] = bcadd($totals['subtotal'], $gross, 6);
            $totals['discount_amount'] = bcadd($totals['discount_amount'], (string) ($line['discount'] ?? '0'), 6);
            $totals['taxable_amount'] = bcadd($totals['taxable_amount'], $calc['line_extension_amount'], 6);
            $totals['tax_amount'] = bcadd($totals['tax_amount'], $calc['tax_amount'], 6);
            $totals['total_amount'] = bcadd($totals['total_amount'], $calc['line_total'], 6);
        }
        $totals['payable_amount'] = bcadd($totals['total_amount'], $totals['rounding_amount'], 6);

        return $totals;
    }
}
