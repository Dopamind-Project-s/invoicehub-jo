<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;
use App\Services\Company\CompanyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

use function setPermissionsTeamId;

class CompanyWorkspaceFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_configuration_uses_teams_with_company_id(): void
    {
        $this->assertTrue(config('permission.teams'));
        $this->assertSame('company_id', config('permission.team_foreign_key'));
        $this->assertDatabaseHas('permissions', ['name' => 'users.manage']);
        $this->assertDatabaseHas('permissions', ['name' => 'invoices.submit']);
    }

    public function test_roles_and_permissions_are_isolated_by_company_team(): void
    {
        [$a, $b] = [Company::create(['legal_name_ar' => 'أ', 'tax_number' => '101']), Company::create(['legal_name_ar' => 'ب', 'tax_number' => '202'])];
        app(CompanyRoleSeeder::class)->seed($a);
        app(CompanyRoleSeeder::class)->seed($b);
        $user = User::factory()->create(['company_id' => $a->id, 'status' => 'active']);
        setPermissionsTeamId($a->id);
        $user->assignRole('Owner');

        $this->assertTrue($user->canInCompany('users.manage', $a->id));
        $this->assertFalse($user->canInCompany('users.manage', $b->id));
        $this->assertNotSame(Role::where('name', 'Owner')->where('company_id', $a->id)->value('id'), Role::where('name', 'Owner')->where('company_id', $b->id)->value('id'));
    }

    public function test_company_user_crud_role_assignment_and_audit(): void
    {
        $company = Company::create(['name_ar' => 'شركة', 'legal_name_ar' => 'شركة', 'tax_number' => '303']);
        app(CompanyRoleSeeder::class)->seed($company);
        $admin = User::factory()->create(['company_id' => $company->id, 'status' => 'active']);
        setPermissionsTeamId($company->id);
        $admin->assignRole('Owner');
        $role = Role::where('name', 'Viewer')->where('company_id', $company->id)->firstOrFail();

        $this->actingAs($admin)->post(route('company.users.store', $company), [
            'name' => 'Workspace User',
            'email' => 'workspace@example.com',
            'phone' => '0790000001',
            'status' => 'active',
            'password' => 'password123',
            'roles' => [$role->id],
        ])->assertRedirect();

        $user = User::where('email', 'workspace@example.com')->firstOrFail();
        $this->assertSame($company->id, $user->company_id);
        setPermissionsTeamId($company->id);
        $this->assertTrue($user->hasRole('Viewer'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'company.user.created', 'auditable_id' => $user->id]);

        $this->actingAs($admin)->post(route('company.users.suspend', [$company, $user]))->assertRedirect();
        $this->assertSame('suspended', $user->refresh()->status);
    }

    public function test_permission_middleware_denies_missing_permission_and_allows_super_admin_bypass(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة', 'tax_number' => '404']);
        app(CompanyRoleSeeder::class)->seed($company);
        $viewer = User::factory()->create(['company_id' => $company->id, 'status' => 'active']);
        setPermissionsTeamId($company->id);
        $viewer->assignRole('Viewer');
        $this->actingAs($viewer)->get(route('company.users.index', $company))->assertForbidden();

        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($superAdmin)->get(route('company.users.index', $company))->assertOk();
    }

    public function test_company_settings_storage_and_activity_center_access(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة', 'tax_number' => '505']);
        app(CompanyRoleSeeder::class)->seed($company);
        $owner = User::factory()->create(['company_id' => $company->id, 'status' => 'active']);
        setPermissionsTeamId($company->id);
        $owner->assignRole('Owner');

        $this->actingAs($owner)->put(route('company.settings.update', $company), [
            'settings' => ['default_language' => 'ar', 'default_currency' => 'JOD', 'invoice_prefix' => 'INV'],
        ])->assertRedirect();

        $this->assertDatabaseHas('company_settings', ['company_id' => $company->id, 'key' => 'invoice_prefix', 'value' => 'INV']);
        $this->assertInstanceOf(CompanySetting::class, CompanySetting::where('key', 'invoice_prefix')->first());

        AuditLog::create(['user_id' => $owner->id, 'action' => 'company.settings.updated', 'auditable_type' => Company::class, 'auditable_id' => $company->id]);
        $this->actingAs($owner)->get(route('company.activity.index', $company))->assertOk()->assertSee('company.settings.updated');
    }

    public function test_authenticated_company_user_can_reach_dashboard(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة', 'tax_number' => '606']);
        $user = User::factory()->create(['company_id' => $company->id, 'status' => 'active']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }
}
