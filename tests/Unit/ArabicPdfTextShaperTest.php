<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Invoices\ArabicPdfTextShaper;
use PHPUnit\Framework\TestCase;

class ArabicPdfTextShaperTest extends TestCase
{
    public function test_it_converts_arabic_to_connected_visual_order_for_the_pdf_fallback(): void
    {
        $shaper = new ArabicPdfTextShaper;

        $this->assertSame('ﺔﻴﺒﻳﺮﺿ ﺓﺭﻮﺗﺎﻓ', $shaper->shapeText('فاتورة ضريبية'));
        $this->assertSame('ﺔﻴﺒﻳﺮﺿ ﺓﺭﻮﺗﺎﻓ / INV-123', $shaper->shapeText('فاتورة ضريبية / INV-123'));
    }

    public function test_it_only_shapes_visible_html_text(): void
    {
        $html = '<style>.label::after{content:"فاتورة"}</style><p title="فاتورة">فاتورة ضريبية</p>';
        $shaped = html_entity_decode((new ArabicPdfTextShaper)->shapeHtml($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $this->assertStringContainsString('content:"فاتورة"', $shaped);
        $this->assertStringContainsString('title="فاتورة"', $shaped);
        $this->assertStringContainsString('ﺔﻴﺒﻳﺮﺿ ﺓﺭﻮﺗﺎﻓ', $shaped);
    }
}
