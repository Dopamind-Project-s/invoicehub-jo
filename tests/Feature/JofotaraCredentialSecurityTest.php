<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Services\Jofotara\JoFotaraCredentialValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JofotaraCredentialSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_jofotara_credentials_are_encrypted_at_rest_and_hidden_from_arrays(): void
    {
        $company = Company::create([
            'legal_name_ar' => 'شركة اختبار',
            'tax_number' => '123456789',
            'jofotara_client_id' => 'client-id-123',
            'jofotara_secret_key' => 'super-secret-key-123456789',
        ]);

        $raw = $company->getRawOriginal();

        $this->assertNotSame('client-id-123', $raw['jofotara_client_id']);
        $this->assertNotSame('super-secret-key-123456789', $raw['jofotara_secret_key']);
        $this->assertSame('client-id-123', $company->jofotara_client_id);
        $this->assertSame('super-secret-key-123456789', $company->jofotara_secret_key);
        $this->assertArrayNotHasKey('jofotara_client_id', $company->toArray());
        $this->assertArrayNotHasKey('jofotara_secret_key', $company->toArray());
    }

    public function test_company_edit_page_does_not_render_stored_jofotara_secrets(): void
    {
        $company = Company::create([
            'legal_name_ar' => 'شركة اختبار',
            'tax_number' => '987654321',
            'jofotara_client_id' => 'client-id-456',
            'jofotara_secret_key' => 'another-super-secret-key',
        ]);

        $this->get(route('companies.edit', $company))
            ->assertOk()
            ->assertDontSee('client-id-456', false)
            ->assertDontSee('another-super-secret-key', false)
            ->assertSee('محفوظ ومشفّر', false);
    }

    public function test_jofotara_config_check_reports_missing_fields_without_secret_values(): void
    {
        config([
            'services.jofotara.client_id' => '',
            'services.jofotara.secret_key' => 'short-secret-value-that-must-not-leak',
            'services.jofotara.source_id' => '',
            'services.jofotara.tax_number' => '',
        ]);

        $this->artisan('jofotara:check-config')
            ->expectsOutputToContain('client_id')
            ->expectsOutputToContain('source_id')
            ->expectsOutputToContain('tax_number')
            ->doesntExpectOutputToContain('short-secret-value-that-must-not-leak')
            ->assertFailed();
    }

    public function test_validator_returns_only_secret_metadata_not_secret_value(): void
    {
        config([
            'services.jofotara.client_id' => 'env-client',
            'services.jofotara.secret_key' => 'valid-secret-key-value',
            'services.jofotara.source_id' => 'source-1',
            'services.jofotara.tax_number' => 'tax-1',
        ]);

        $result = app(JoFotaraCredentialValidator::class)->validate();

        $this->assertTrue($result['valid']);
        $this->assertSame(22, $result['meta']['secret_key_length']);
        $this->assertStringNotContainsString('valid-secret-key-value', json_encode($result, JSON_THROW_ON_ERROR));
    }
}
