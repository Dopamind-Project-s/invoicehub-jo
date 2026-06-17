<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompareJofotaraRequest extends Command
{
    protected $signature = 'jofotara:compare-request {invoice_id=1}';

    protected $description = 'Compare saved JoFotara XML and payload hashes.';

    public function handle(): int
    {
        $id = $this->argument('invoice_id');
        $xmlPath = storage_path('app/jofotara/last-submission-'.$id.'.xml');
        $payloadPath = storage_path('app/jofotara/last-payload-'.$id.'.json');

        if (! file_exists($xmlPath) || ! file_exists($payloadPath)) {
            $this->error('Expected XML or payload file is missing.');
            $this->line('XML path: '.$xmlPath);
            $this->line('Payload path: '.$payloadPath);

            return self::FAILURE;
        }

        $xml = file_get_contents($xmlPath) ?: '';
        $payload = json_decode(file_get_contents($payloadPath) ?: '{}', true);
        $base64 = (string) data_get($payload, 'invoice', '');

        $this->line('XML length: '.strlen($xml));
        $this->line('Base64 length: '.strlen($base64));
        $this->line('SHA256 of XML: '.hash('sha256', $xml));
        $this->line('SHA256 of Base64: '.hash('sha256', $base64));

        return self::SUCCESS;
    }
}
