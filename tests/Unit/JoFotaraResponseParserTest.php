<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Jofotara\JoFotaraResponseParser;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response;
use PHPUnit\Framework\TestCase;

class JoFotaraResponseParserTest extends TestCase
{
    public function test_it_reads_known_qr_and_uuid_variations(): void
    {
        $response = new Response(new PsrResponse(200, [], json_encode(['EINV_NUM' => 'SUB-1', 'EINV_QR' => 'QR-VALUE'])));
        $parsed = (new JoFotaraResponseParser)->parse($response);

        $this->assertTrue($parsed['accepted']);
        $this->assertSame('SUB-1', $parsed['uuid']);
        $this->assertSame('QR-VALUE', $parsed['qr']);
    }
}
