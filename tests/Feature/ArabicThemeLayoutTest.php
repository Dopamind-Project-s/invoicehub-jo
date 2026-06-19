<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArabicThemeLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_layout_uses_arabic_rtl_theme_shell(): void
    {
        $this->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl"', false)
            ->assertSee('app-sidebar', false)
            ->assertSee('app-topbar', false)
            ->assertSee('vendor/zaha-theme/css/theme.css', false)
            ->assertSee('css/phase1-layout.css', false)
            ->assertSee('data-theme-toggle', false);
    }
}
