<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Jofotara\JoFotaraResponseParser;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response;
use PHPUnit\Framework\TestCase;

class JoFotaraResponseParserTest extends TestCase
{
    public function test_pass_validation_and_submitted_status_are_separate_from_accepted(): void
    {
        $response = new Response(new PsrResponse(200, [], json_encode(['EINV_INV_UUID' => 'UUID-1', 'EINV_QR' => 'QR-1', 'EINV_STATUS' => 'SUBMITTED', 'EINV_RESULTS' => ['status' => 'PASS']])));
        $parsed = (new JoFotaraResponseParser)->parse($response);

        $this->assertSame('SUBMITTED', $parsed['status']);
        $this->assertSame('PASS', $parsed['validation_result']);
        $this->assertNotSame('ACCEPTED', $parsed['status']);
        $this->assertNotSame('ACCEPTED', $parsed['validation_result']);
    }

    public function test_errors_are_prioritized_over_info_messages(): void
    {
        $response = new Response(new PsrResponse(200, [], json_encode([
            'EINV_STATUS' => 'NOT_SUBMITTED',
            'EINV_RESULTS' => [
                'status' => 'ERROR',
                'INFO' => [['INFO_MESSAGE' => 'Complied with UBL 2.1 standards']],
                'ERRORS' => [['ERROR_MESSAGE' => 'Issue date cannot be in the future']],
            ],
        ])));
        $parsed = (new JoFotaraResponseParser)->parse($response);

        $this->assertSame('ERROR', $parsed['validation_result']);
        $this->assertSame('Issue date cannot be in the future', $parsed['error_summary']);
        $this->assertStringNotContainsString('Complied with UBL', $parsed['error_summary']);
    }

    public function test_it_reads_known_qr_and_uuid_variations(): void
    {
        $response = new Response(new PsrResponse(200, [], json_encode(['EINV_NUM' => 'SUB-1', 'EINV_QR' => 'QR-VALUE'])));
        $parsed = (new JoFotaraResponseParser)->parse($response);

        $this->assertTrue($parsed['accepted']);
        $this->assertSame('SUB-1', $parsed['uuid']);
        $this->assertSame('QR-VALUE', $parsed['qr']);
    }
}
