<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountManagementRepairTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_contract_survives_cache_hits_and_incompatible_cache(): void
    {
        $admin = User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail();
        Company::create(['legal_name_ar' => 'الاسم القانوني', 'tax_number' => 'DASH-1']);
        Company::create(['legal_name_ar' => '', 'name_ar' => null, 'name_en' => 'English name', 'tax_number' => 'DASH-2']);
        Cache::put(AdminDashboardService::CACHE_KEY, ['latest_companies' => ['broken']], 300);
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('الاسم القانوني')->assertSee('English name');
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'user']))->get('/admin/dashboard')->assertForbidden();
    }

    public function test_company_creation_is_atomic_and_creates_flagged_company_admin(): void
    {
        $admin = User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail();
        $response = $this->actingAs($admin)->post(route('admin.companies.store'), $this->companyPayload());
        $company = Company::where('tax_number', 'NEW-100')->firstOrFail();
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $response->assertRedirect(route('admin.companies.show', $company));
        $this->assertSame($company->id, $user->company_id);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertTrue($user->must_change_password);
        setPermissionsTeamId($company->id);
        $this->assertTrue($user->hasRole('Company Admin'));
        $this->assertFalse($user->isSuperAdmin());

        $this->actingAs($admin)->post(route('admin.companies.store'), array_merge($this->companyPayload(), ['tax_number' => 'NEW-101']))->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('companies', ['tax_number' => 'NEW-101']);
    }

    public function test_direct_subscription_and_rest_actions_are_validated_and_audited(): void
    {
        $admin = User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail();
        $company = Company::create(['legal_name_ar' => 'بلا اشتراك', 'tax_number' => 'SUB-1', 'email' => 'sub@example.com']);
        $plan = Plan::where('is_active', true)->firstOrFail();
        $this->actingAs($admin)->get(route('admin.companies.subscriptions.index', $company))->assertOk()->assertSee('لا يوجد اشتراك مسجل لهذه المنشأة');
        $this->actingAs($admin)->post(route('admin.companies.subscriptions.store', $company), ['plan_id' => $plan->id, 'billing_cycle' => 'monthly', 'start_date' => today()->toDateString()])->assertRedirect();
        $subscription = Subscription::where('company_id', $company->id)->firstOrFail();
        $this->assertSame('admin_direct', $subscription->source);
        $this->actingAs($admin)->post(route('admin.companies.subscriptions.renew', $company), ['billing_cycle' => 'invalid'])->assertSessionHasErrors('billing_cycle');
        $this->actingAs($admin)->patch(route('admin.companies.subscriptions.auto-renew', $company))->assertRedirect();
        $this->assertTrue($subscription->refresh()->auto_renew);
        $this->actingAs($admin)->post(route('admin.companies.subscriptions.cancel', $company))->assertRedirect();
        $this->assertSame('cancelled', $subscription->refresh()->status);
        $this->actingAs($admin)->post(route('admin.companies.subscriptions.reactivate', $company))->assertRedirect();
        $this->assertSame('active', $subscription->refresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.subscription.direct_created']);
        $this->get(route('admin.companies.subscriptions.cancel', $company))->assertMethodNotAllowed();
    }

    public function test_flagged_user_must_change_password_and_sensitive_fields_are_not_profile_editable(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة', 'tax_number' => 'PROF-1']);
        $user = User::factory()->create(['company_id' => $company->id, 'must_change_password' => true, 'password' => Hash::make('password')]);
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('profile.edit'));
        $this->actingAs($user)->get(route('profile.edit'))->assertOk();
        $this->actingAs($user)->put(route('profile.password.update'), ['current_password' => 'wrong', 'password' => 'New-secure-password-123!', 'password_confirmation' => 'New-secure-password-123!'])->assertSessionHasErrors('current_password');
        $this->actingAs($user)->put(route('profile.password.update'), ['current_password' => 'password', 'password' => 'New-secure-password-123!', 'password_confirmation' => 'New-secure-password-123!'])->assertRedirect(route('profile.edit'));
        $this->assertFalse($user->refresh()->must_change_password);
        $this->assertTrue(Hash::check('New-secure-password-123!', $user->password));
        $other = Company::create(['legal_name_ar' => 'أخرى', 'tax_number' => 'PROF-2']);
        $this->actingAs($user)->patch(route('profile.update'), ['name' => 'Safe', 'email' => $user->email, 'company_id' => $other->id, 'role' => User::ROLE_SUPER_ADMIN]);
        $this->assertSame($company->id, $user->refresh()->company_id);
        $this->assertFalse($user->isSuperAdmin());
    }

    private function companyPayload(): array
    {
        return ['name_ar' => 'منشأة جديدة', 'name_en' => 'New', 'tax_number' => 'NEW-100', 'email' => ' New@Example.com ', 'status' => 'active', 'default_language' => 'ar', 'default_currency' => 'JOD'];
    }
}
