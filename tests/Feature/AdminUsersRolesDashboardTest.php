<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Plan;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Services\Company\CompanyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

use function setPermissionsTeamId;

class AdminUsersRolesDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_shows_system_metrics_not_single_company_metrics(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@invosync.local')->firstOrFail();
        SubscriptionRequest::create(['company_name' => 'طلب لوحة', 'applicant_name' => 'طالب', 'email' => 'dash@example.com', 'phone' => '079', 'plan_id' => Plan::firstOrFail()->id, 'billing_cycle' => 'yearly', 'status' => 'pending']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('مؤشرات المنصة والاشتراكات')
            ->assertSee('إجمالي المنشآت')
            ->assertSee('أحدث طلبات الاشتراك')
            ->assertDontSee('عدد منتجات شركة محددة');
    }

    public function test_super_admin_dashboard_ignores_stale_scalar_cached_latest_requests(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@invosync.local')->firstOrFail();
        Cache::put('admin-dashboard:v3', [
            'total_companies' => 0,
            'active_companies' => 0,
            'pending_requests' => 0,
            'active_subscriptions' => 0,
            'expiring_subscriptions' => 0,
            'expired_subscriptions' => 0,
            'contacted_requests' => 0,
            'inactive_companies' => 0,
            'blogs_total' => 0,
            'blogs_published' => 0,
            'blogs_drafts' => 0,
            'alerts' => [],
            'latest_requests' => ['stale-company-name'],
            'latest_companies' => collect(),
            'latest_audits' => collect(),
        ], 300);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('أحدث طلبات الاشتراك')
            ->assertDontSee('stale-company-name');
    }

    public function test_super_admin_can_create_role_and_user_and_last_super_admin_is_protected(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@invosync.local')->firstOrFail();
        $permission = Permission::where('name', 'manage_users')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'Support Admin',
            'permissions' => [$permission->name],
        ])->assertRedirect();

        $role = Role::where('name', 'Support Admin')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('manage_users'));

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Support User',
            'email' => 'support@example.com',
            'status' => 'active',
            'role' => 'user',
            'roles' => [$role->id],
            'send_reset_link' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'support@example.com']);
        $this->actingAs($admin)->post(route('admin.users.suspend', $admin))->assertSessionHasErrors(['status']);
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertSessionHasErrors(['user']);
    }

    public function test_non_super_admin_cannot_manage_system_users_or_roles(): void
    {
        $this->seed();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_company_admin_sees_company_dashboard_and_data_entry_does_not_see_user_management(): void
    {
        $this->seed();
        $companyA = Company::create(['name_ar' => 'شركة أ', 'legal_name_ar' => 'شركة أ', 'tax_number' => 'A-100', 'status' => 'active', 'is_active' => true]);
        $companyB = Company::create(['name_ar' => 'شركة ب', 'legal_name_ar' => 'شركة ب', 'tax_number' => 'B-100', 'status' => 'active', 'is_active' => true]);
        app(CompanyRoleSeeder::class)->seed($companyA);
        app(CompanyRoleSeeder::class)->seed($companyB);
        Contact::create(['company_id' => $companyA->id, 'name_ar' => 'عميل أ', 'type' => 'customer']);

        $admin = User::factory()->create(['company_id' => $companyA->id, 'role' => 'user', 'status' => 'active']);
        $entry = User::factory()->create(['company_id' => $companyA->id, 'role' => 'user', 'status' => 'active']);
        setPermissionsTeamId($companyA->id);
        $admin->syncRoles([Role::where('company_id', $companyA->id)->where('name', 'Company Admin')->firstOrFail()]);
        $entry->syncRoles([Role::where('company_id', $companyA->id)->where('name', 'Company Data Entry')->firstOrFail()]);

        $this->actingAs($admin)->get(route('company.dashboard', $companyA))
            ->assertOk()
            ->assertSee('شركة أ')
            ->assertSee('المستخدمون');

        $this->actingAs($entry)->get(route('company.dashboard', $companyA))
            ->assertOk()
            ->assertSee('إنشاء فاتورة سريعة')
            ->assertDontSee('المستخدمون');

        $this->actingAs($entry)->get(route('company.users.index', $companyA))->assertForbidden();
        $this->actingAs($admin)->get(route('company.products.index', $companyB))->assertNotFound();
    }
}
