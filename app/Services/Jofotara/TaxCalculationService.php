<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

class TaxCalculationService
{
    private const SCALE = 9;

    public function calculateLine(string $quantity, string $unitPrice, string $discount, string $taxPercent): array
    {
        $gross = $this->round(bcmul($quantity, $unitPrice, self::SCALE + 2));
        $net = $this->round(bcsub($gross, $discount, self::SCALE + 2));
        if (bccomp($net, '0', self::SCALE) < 0) {
            $net = $this->zero();
        }
        $taxRate = bcdiv($taxPercent, '100', self::SCALE + 2);
        $tax = $this->round(bcmul($net, $taxRate, self::SCALE + 2));
        $rounding = $this->round(bcadd($net, $tax, self::SCALE + 2));

        return [
            'gross_amount' => $gross,
            'line_extension_amount' => $net,
            'tax_amount' => $tax,
            'rounding_amount' => $rounding,
            'line_total' => $rounding,
        ];
    }

    public function calculateInvoice(iterable $lines): array
    {
        $totals = [
            'subtotal' => $this->zero(),
            'discount_amount' => $this->zero(),
            'taxable_amount' => $this->zero(),
            'tax_amount' => $this->zero(),
            'total_amount' => $this->zero(),
            'rounding_amount' => $this->zero(),
            'payable_amount' => $this->zero(),
        ];

        foreach ($lines as $line) {
            $calc = $this->calculateLine((string) $line['quantity'], (string) $line['unit_price'], (string) ($line['discount'] ?? '0'), (string) ($line['tax_percent'] ?? '0'));
            $totals['subtotal'] = $this->round(bcadd($totals['subtotal'], $calc['gross_amount'], self::SCALE + 2));
            $totals['discount_amount'] = $this->round(bcadd($totals['discount_amount'], (string) ($line['discount'] ?? '0'), self::SCALE + 2));
            $totals['taxable_amount'] = $this->round(bcadd($totals['taxable_amount'], $calc['line_extension_amount'], self::SCALE + 2));
            $totals['tax_amount'] = $this->round(bcadd($totals['tax_amount'], $calc['tax_amount'], self::SCALE + 2));
            $totals['total_amount'] = $this->round(bcadd($totals['total_amount'], $calc['rounding_amount'], self::SCALE + 2));
        }

        $totals['payable_amount'] = $this->round(bcadd($totals['total_amount'], $totals['rounding_amount'], self::SCALE + 2));

        return $totals;
    }

    public function totalsMatch(array $expected, array $actual): bool
    {
        foreach (['subtotal', 'discount_amount', 'taxable_amount', 'tax_amount', 'total_amount', 'payable_amount'] as $key) {
            if (bccomp((string) $expected[$key], (string) $actual[$key], 6) !== 0) {
                return false;
            }
        }

        return true;
    }

    private function round(string $value): string
    {
        if (! str_contains($value, '.')) {
            return $value.'.'.str_repeat('0', self::SCALE);
        }

        [$whole, $decimal] = explode('.', $value, 2);
        $decimal = str_pad(substr($decimal, 0, self::SCALE), self::SCALE, '0');

        return $whole.'.'.$decimal;
    }

    private function zero(): string
    {
        return '0.'.str_repeat('0', self::SCALE);
    }
}
