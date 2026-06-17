<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use Illuminate\Http\Client\Response;

class JoFotaraResponseParser
{
    public function parse(Response $response): array
    {
        $raw = $response->body();
        $json = $response->json();
        $body = is_array($json) ? $json : ['raw' => $raw];
        $uuid = $this->first($body, ['uuid', 'invoice_uuid', 'EINV_NUM', 'submission_uuid']);
        $qr = $this->first($body, ['qr', 'qr_code', 'EINV_QR', 'invoiceQr']);
        $errors = $this->first($body, ['errors', 'message', 'validationErrors', 'ErrorMessage']);
        $accepted = $response->successful() && $raw !== '' && ! $errors;

        return [
            'accepted' => $accepted,
            'empty' => $raw === '',
            'uuid' => $uuid,
            'qr' => $qr,
            'errors' => $errors,
            'body' => $body,
            'raw_response' => $raw,
        ];
    }

    private function first(array $body, array $keys): mixed
    {
        foreach ($keys as $key) {
            $value = data_get($body, $key);
            if (filled($value)) {
                return $value;
            }
        }

        foreach ($body as $value) {
            if (is_array($value)) {
                $nested = $this->first($value, $keys);
                if (filled($nested)) {
                    return $nested;
                }
            }
        }

        return null;
    }
}
