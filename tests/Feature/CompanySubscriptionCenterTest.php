<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionChangeRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySubscriptionCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_company_show_is_single_page_without_old_tabs(): void
    {
        $admin = User::where('email', 'admin@invosync.local')->firstOrFail();
        $company = Company::where('tax_number', '9578331')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.companies.show', $company))
            ->assertOk()
            ->assertSee('ملخص تشغيلي')
            ->assertSee('إدارة الاشتراكات')
            ->assertDontSee('id="companyTabs"', false)
            ->assertDontSee('data-company-tab', false)
            ->assertDontSee('إعدادات جوفوتارا</button>', false);
    }

    public function test_admin_subscriptions_page_history_and_actions_work(): void
    {
        $admin = User::where('email', 'admin@invosync.local')->firstOrFail();
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $subscription = Subscription::where('company_id', $company->id)->where('source', 'demo-current')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.companies.subscriptions.index', $company))
            ->assertOk()
            ->assertSee('سجل الاشتراكات')
            ->assertSee('demo-previous-monthly')
            ->assertSee('Renew Monthly')
            ->assertSee('Subscription Timeline')
            ->assertSee('Renewal Summary')
            ->assertSee('طرق الدفع')
            ->assertSee('Coming Soon')
            ->assertSee('Subscription Events');

        $this->actingAs($admin)->post(route('admin.companies.subscriptions.toggle-auto-renew', $company))->assertRedirect();
        $this->assertNotSame($subscription->auto_renew, $subscription->refresh()->auto_renew);

        $this->actingAs($admin)->post(route('admin.companies.subscriptions.renew', [$company, 'monthly']))->assertRedirect();
        $this->assertSame('monthly', $subscription->refresh()->billing_cycle);
    }

    public function test_company_subscription_page_and_change_request(): void
    {
        $user = User::where('email', 'company@invosync.local')->firstOrFail();
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $plan = Plan::where('slug', 'business')->firstOrFail();

        $this->actingAs($user)->get(route('company.subscriptions.index', $company))
            ->assertOk()
            ->assertSee('طلب تغيير اشتراك')
            ->assertSee('سجل الاشتراكات السابقة')
            ->assertSee('Timeline')
            ->assertSee('طرق الدفع')
            ->assertSee('Coming Soon');

        $this->actingAs($user)->post(route('company.subscriptions.requests.store', $company), [
            'request_type' => 'upgrade',
            'requested_plan_id' => $plan->id,
            'billing_cycle' => 'yearly',
            'notes' => 'نرغب بالترقية',
        ])->assertRedirect();

        $this->assertDatabaseHas('subscription_events', ['company_id' => $company->id, 'event_type' => 'change_requested']);

        $this->assertDatabaseHas('subscription_change_requests', [
            'company_id' => $company->id,
            'requested_plan_id' => $plan->id,
            'request_type' => 'upgrade',
            'status' => 'pending',
        ]);
    }


    public function test_admin_plans_page_has_comparison_table_and_recommended_badge(): void
    {
        $admin = User::where('email', 'admin@invosync.local')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.plans.index'))
            ->assertOk()
            ->assertSee('جدول مقارنة الباقات')
            ->assertSee('Recommended')
            ->assertSee('JoFotara')
            ->assertSee('✓');
    }

    public function test_company_dashboard_has_subscription_widget(): void
    {
        $user = User::where('email', 'company@invosync.local')->firstOrFail();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('الاشتراك الحالي')
            ->assertSee('إدارة الاشتراك')
            ->assertSee('Auto Renew');
    }

    public function test_seeders_are_idempotent_and_demo_data_exists(): void
    {
        $this->seed(DatabaseSeeder::class);

        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $this->assertDatabaseHas('product_categories', ['company_id' => $company->id, 'name_ar' => 'وجبات']);
        $this->assertDatabaseHas('units', ['company_id' => $company->id, 'name_ar' => 'خدمة']);
        $this->assertDatabaseHas('tax_profiles', ['company_id' => $company->id, 'name' => 'ضريبة 16%']);
        $this->assertDatabaseHas('products', ['company_id' => $company->id, 'name_ar' => 'وجبة مشاوي مشكلة']);
        $this->assertDatabaseHas('contacts', ['company_id' => $company->id, 'name_ar' => 'عميل ضريبي']);
        $this->assertSame(3, Subscription::where('company_id', $company->id)->count());
        $this->assertDatabaseHas('subscription_events', ['company_id' => $company->id, 'event_type' => 'subscription_created']);
        $this->assertDatabaseHas('subscription_change_requests', ['company_id' => $company->id, 'request_type' => 'upgrade']);
    }
}
