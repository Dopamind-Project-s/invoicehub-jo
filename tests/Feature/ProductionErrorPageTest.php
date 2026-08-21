<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ProductionErrorPageTest extends TestCase
{
    public function test_production_exception_page_is_generic_and_does_not_disclose_sensitive_context(): void
    {
        config(['app.debug' => false]);
        app()->detectEnvironment(fn (): string => 'production');
        Route::middleware('web')->get('/_test/production-error', function (): never {
            throw new RuntimeException('Forced production rendering test failure.');
        });

        $response = $this->withHeader('Authorization', 'Bearer secret')->get('/_test/production-error');

        $response->assertStatus(500)->assertSee('حدث خطأ غير متوقع');
        foreach (['Stack trace', 'SQLSTATE', 'vendor/', 'resources/views/', 'authorization', 'laravel-session', 'APP_KEY'] as $disclosure) {
            $response->assertDontSee($disclosure, false);
        }
    }

    public function test_custom_arabic_error_pages_render(): void
    {
        foreach ([403, 404, 419, 429, 500, 503] as $status) {
            $this->view("errors.{$status}")->assertSee((string) $status)->assertSee('العودة إلى الصفحة الرئيسية');
        }
    }
}
