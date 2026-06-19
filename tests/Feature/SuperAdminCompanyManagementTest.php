<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\FeatureKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminCompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_admin_routes_are_protected_by_super_admin_role(): void
    {
        $this->get(route('admin.dashboard'))->assertForbidden();

        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_super_admin_can_create_company_assign_features_and_audit_changes(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $features = FeatureKey::whereIn('code', ['INVOICES', 'JOFOTARA_SUBMIT'])->pluck('id')->all();

        $response = $this->actingAs($admin)->post(route('admin.companies.store'), [
            'name_ar' => 'شركة الإدارة',
            'name_en' => 'Admin Company',
            'tax_number' => '44556677',
            'national_number' => '99887766',
            'phone' => '0790000000',
            'email' => 'admin-company@example.com',
            'status' => 'active',
            'jofotara_source_id' => 'SRC-1',
            'jofotara_client_id' => 'client-admin',
            'jofotara_secret_key' => 'admin-secret-key-123456',
            'default_language' => 'ar',
            'default_currency' => 'JOD',
            'feature_keys' => $features,
        ]);

        $company = Company::where('tax_number', '44556677')->firstOrFail();
        $response->assertRedirect(route('admin.companies.show', $company));
        $this->assertSame('شركة الإدارة', $company->name_ar);
        $this->assertSame('شركة الإدارة', $company->legal_name_ar);
        $this->assertTrue($company->hasJofotaraSecretKey());
        $this->assertCount(2, $company->featureKeys);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.company.created', 'auditable_id' => $company->id]);
        $this->assertStringNotContainsString('admin-secret-key-123456', AuditLog::where('auditable_id', $company->id)->get()->toJson());
    }

    public function test_super_admin_can_suspend_and_activate_company_with_audit_logs(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $company = Company::create(['name_ar' => 'شركة', 'legal_name_ar' => 'شركة', 'tax_number' => '777888999', 'status' => 'active', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.companies.suspend', $company))->assertRedirect();
        $this->assertTrue($company->refresh()->isSuspended());
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.company.suspended', 'auditable_id' => $company->id]);

        $this->actingAs($admin)->post(route('admin.companies.activate', $company))->assertRedirect();
        $this->assertFalse($company->refresh()->isSuspended());
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.company.activated', 'auditable_id' => $company->id]);
    }
}
