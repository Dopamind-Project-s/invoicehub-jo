<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Company;

class JoFotaraCredentialValidator
{
    public function validate(?Company $company = null): array
    {
        $clientId = $company?->jofotara_client_id ?: config('services.jofotara.client_id');
        $secretKey = $company?->jofotara_secret_key ?: config('services.jofotara.secret_key');
        $sourceId = $company?->jofotara_source_id ?: config('services.jofotara.source_id');
        $taxNumber = $company?->tax_number ?: config('services.jofotara.tax_number');

        $errors = [];
        if (blank(config('services.jofotara.url'))) {
            $errors['url'] = 'JoFotara API URL is missing.';
        }
        if (blank($clientId)) {
            $errors['client_id'] = 'JoFotara Client ID is missing.';
        }
        if (blank($secretKey)) {
            $errors['secret_key'] = 'JoFotara Secret Key is missing.';
        }
        if (blank($sourceId)) {
            $errors['source_id'] = 'JoFotara source ID is missing.';
        }
        if (blank($taxNumber)) {
            $errors['tax_number'] = 'Seller tax number is missing.';
        }
        if (filled($clientId) && strlen((string) $clientId) > 100) {
            $errors['client_id'] = 'JoFotara Client ID is longer than 100 characters.';
        }
        if (filled($secretKey) && strlen((string) $secretKey) < 16) {
            $errors['secret_key'] = 'JoFotara Secret Key is too short.';
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'meta' => [
                'url_configured' => filled(config('services.jofotara.url')),
                'client_id_configured' => filled($clientId),
                'secret_key_configured' => filled($secretKey),
                'secret_key_length' => filled($secretKey) ? strlen((string) $secretKey) : 0,
                'source_id_configured' => filled($sourceId),
                'tax_number_configured' => filled($taxNumber),
            ],
        ];
    }
}
