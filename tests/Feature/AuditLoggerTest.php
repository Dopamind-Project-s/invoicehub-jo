<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Services\Audit\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logger_records_generic_model_changes_with_request_context(): void
    {
        $company = Company::create([
            'legal_name_ar' => 'شركة تدقيق',
            'tax_number' => '555444333',
        ]);
        $request = Request::create('/companies/'.$company->id, 'PUT', server: [
            'REMOTE_ADDR' => '127.0.0.44',
            'HTTP_USER_AGENT' => 'Audit Test Agent',
        ]);

        $log = app(AuditLogger::class)->record(
            action: 'company.updated',
            model: $company,
            before: ['legal_name_ar' => 'قديم'],
            after: ['legal_name_ar' => 'جديد'],
            request: $request,
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'company.updated',
            'auditable_type' => Company::class,
            'auditable_id' => $company->id,
            'ip_address' => '127.0.0.44',
        ]);
        $this->assertSame(['legal_name_ar' => 'قديم'], $log->before_values);
        $this->assertSame(['legal_name_ar' => 'جديد'], $log->after_values);
        $this->assertSame('Audit Test Agent', $log->user_agent);
    }

    public function test_audit_logger_redacts_sensitive_values(): void
    {
        $company = Company::create([
            'legal_name_ar' => 'شركة تدقيق',
            'tax_number' => '111222333',
        ]);

        $log = app(AuditLogger::class)->record(
            action: 'company.credentials.updated',
            model: $company,
            before: ['jofotara_secret_key' => 'old-secret', 'nested' => ['token' => 'abc']],
            after: ['jofotara_client_id' => 'client', 'jofotara_secret_key' => 'new-secret'],
        );

        $this->assertSame('[redacted]', $log->before_values['jofotara_secret_key']);
        $this->assertSame('[redacted]', $log->before_values['nested']['token']);
        $this->assertSame('[redacted]', $log->after_values['jofotara_client_id']);
        $this->assertSame('[redacted]', $log->after_values['jofotara_secret_key']);
        $this->assertStringNotContainsString('new-secret', AuditLog::firstOrFail()->toJson());
    }
}
