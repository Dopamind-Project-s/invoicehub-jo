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
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl"', false)
            ->assertSee('css/Theme.css', false)
            ->assertSee('css/Style.css', false)
            ->assertSee('css/phase1-layout.css', false)
            ->assertDontSee('@vite', false)
            ->assertDontSee('public/build/manifest.json', false);
    }
}
