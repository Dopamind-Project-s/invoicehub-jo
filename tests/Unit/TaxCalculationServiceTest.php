<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Jofotara\TaxCalculationService;
use PHPUnit\Framework\TestCase;

class TaxCalculationServiceTest extends TestCase
{
    public function test_it_calculates_line_totals_with_bcmath_precision(): void
    {
        $result = (new TaxCalculationService)->calculateLine('2', '10.125000', '0.250000', '16');

        $this->assertSame('20.000000', $result['line_extension_amount']);
        $this->assertSame('3.200000', $result['tax_amount']);
        $this->assertSame('23.200000', $result['line_total']);
    }
}
