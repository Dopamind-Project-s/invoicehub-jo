<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Services\Admin\SubscriptionRequestProvisioningService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSubscriptionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_subscription_request_and_super_admin_can_approve_it(): void
    {
        $this->seed();
        $plan = Plan::where('is_active', true)->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('subscription-requests.create', ['plan' => $plan->slug ?: $plan->id]), false)
            ->assertSee('طلب الاشتراك');

        $this->get(route('subscription-requests.create', ['plan' => $plan->slug ?: $plan->id]))
            ->assertOk()
            ->assertSee('أكمل بياناتك')
            ->assertSee($plan->name_ar ?: $plan->name);

        $response = $this->post(route('subscription-requests.store'), [
            'company_name' => 'شركة تجربة الاشتراك',
            'applicant_name' => 'أحمد مقدم الطلب',
            'email' => 'requester@example.com',
            'phone' => '0790000000',
            'whatsapp' => '0791111111',
            'plan_id' => $plan->id,
            'billing_cycle' => 'yearly',
            'notes' => 'نرغب بالتواصل صباحًا.',
        ]);

        $response->assertRedirect(route('subscription-requests.thank-you'));
        $this->assertDatabaseHas('subscription_requests', [
            'company_name' => 'شركة تجربة الاشتراك',
            'email' => 'requester@example.com',
            'plan_id' => $plan->id,
            'billing_cycle' => 'yearly',
            'status' => SubscriptionRequest::STATUS_PENDING,
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $subscriptionRequest = SubscriptionRequest::firstOrFail();

        $this->actingAs($admin)->get(route('admin.subscription-requests.index'))
            ->assertOk()
            ->assertSee('شركة تجربة الاشتراك')
            ->assertSee('pending');

        $this->actingAs($admin)->put(route('admin.subscription-requests.update', $subscriptionRequest), [
            'status' => SubscriptionRequest::STATUS_APPROVED,
            'admin_notes' => 'تم التواصل وسيتم التجهيز يدويًا.',
        ])->assertRedirect(route('admin.subscription-requests.show', $subscriptionRequest));

        $subscriptionRequest->refresh();
        $this->assertSame(SubscriptionRequest::STATUS_APPROVED, $subscriptionRequest->status);
        $this->assertSame($admin->id, $subscriptionRequest->approved_by);
        $this->assertNotNull($subscriptionRequest->approved_at);
    }

    public function test_subscription_request_validation_rejects_invalid_plan_and_billing_cycle(): void
    {
        $this->seed();

        $this->post(route('subscription-requests.store'), [
            'company_name' => 'شركة غير مكتملة',
            'applicant_name' => 'مقدم الطلب',
            'email' => 'not-an-email',
            'phone' => '0790000000',
            'plan_id' => 999999,
            'billing_cycle' => 'weekly',
        ])->assertSessionHasErrors(['email', 'plan_id', 'billing_cycle']);
    }

    public function test_super_admin_can_open_prefilled_provisioning_form_and_provision_request(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $plan = Plan::where('is_active', true)->firstOrFail();
        $request = SubscriptionRequest::create([
            'company_name' => 'شركة تجهيز من الطلب',
            'applicant_name' => 'سارة المالكة',
            'email' => 'owner-provision@example.com',
            'phone' => '0792222222',
            'whatsapp' => '0793333333',
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'notes' => 'ملاحظة من الطلب',
            'status' => SubscriptionRequest::STATUS_APPROVED,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.subscription-requests.provision.create', $request))
            ->assertOk()
            ->assertSee('شركة تجهيز من الطلب')
            ->assertSee('سارة المالكة')
            ->assertSee('owner-provision@example.com')
            ->assertSee('0793333333')
            ->assertSee('monthly');

        $this->actingAs($admin)->post(route('admin.subscription-requests.provision.store', $request), $this->provisionPayload($request, [
            'tax_number' => '555777999',
        ]))->assertRedirect(route('admin.subscription-requests.show', $request));

        $request->refresh();
        $company = Company::findOrFail($request->company_id);
        $user = User::findOrFail($request->user_id);
        $subscription = Subscription::findOrFail($request->subscription_id);

        $this->assertSame(SubscriptionRequest::STATUS_PROVISIONED, $request->status);
        $this->assertSame('شركة تجهيز من الطلب', $company->name_ar);
        $this->assertSame($company->id, $user->company_id);
        $this->assertSame('سارة المالكة', $user->name);
        $this->assertSame($plan->id, $subscription->plan_id);
        $this->assertSame('monthly', $subscription->billing_cycle);
        $this->assertNotNull($subscription->current_period_start_at);
        $this->assertNotNull($subscription->current_period_end_at);
        $this->assertTrue($subscription->current_period_end_at->isSameDay($subscription->current_period_start_at->copy()->addMonth()));
        $this->assertSame($admin->id, $request->provisioned_by);
        $this->assertNotNull($request->provisioned_at);
        $this->assertTrue($company->featureKeys()->count() >= $plan->featureKeys()->count());
    }

    public function test_provisioning_validation_rejects_invalid_plan_and_billing_cycle(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $request = SubscriptionRequest::create([
            'company_name' => 'شركة تحقق',
            'applicant_name' => 'مالك',
            'email' => 'valid-provision@example.com',
            'phone' => '0790000000',
            'plan_id' => Plan::firstOrFail()->id,
            'billing_cycle' => 'yearly',
            'status' => SubscriptionRequest::STATUS_APPROVED,
        ]);

        $payload = $this->provisionPayload($request, ['plan_id' => 999999, 'billing_cycle' => 'weekly', 'tax_number' => '333222111']);

        $this->actingAs($admin)->post(route('admin.subscription-requests.provision.store', $request), $payload)
            ->assertSessionHasErrors(['plan_id', 'billing_cycle']);
    }

    public function test_provisioning_same_request_twice_is_blocked(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $plan = Plan::where('is_active', true)->firstOrFail();
        $request = SubscriptionRequest::create([
            'company_name' => 'شركة منع التكرار',
            'applicant_name' => 'مالك التكرار',
            'email' => 'duplicate-block@example.com',
            'phone' => '0790000000',
            'plan_id' => $plan->id,
            'billing_cycle' => 'yearly',
            'status' => SubscriptionRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)->post(route('admin.subscription-requests.provision.store', $request), $this->provisionPayload($request, ['tax_number' => '10101010']))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.subscription-requests.provision.store', $request->fresh()), $this->provisionPayload($request->fresh(), ['tax_number' => '20202020', 'owner_email' => 'second-owner@example.com']))->assertSessionHasErrors(['provision']);

        $this->assertSame(1, Company::whereIn('tax_number', ['10101010', '20202020'])->count());
    }

    public function test_provisioning_rolls_back_if_later_step_fails(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        User::factory()->create(['email' => 'already-used@example.com']);
        $request = SubscriptionRequest::create([
            'company_name' => 'شركة رول باك',
            'applicant_name' => 'مالك',
            'email' => 'already-used@example.com',
            'phone' => '0790000000',
            'plan_id' => Plan::where('is_active', true)->firstOrFail()->id,
            'billing_cycle' => 'yearly',
            'status' => SubscriptionRequest::STATUS_APPROVED,
        ]);

        try {
            app(SubscriptionRequestProvisioningService::class)->provision($request, $this->provisionPayload($request, ['tax_number' => '90909090', 'owner_email' => 'already-used@example.com']), $admin);
            $this->fail('Expected duplicate user email to fail provisioning.');
        } catch (QueryException) {
            $this->assertDatabaseMissing('companies', ['tax_number' => '90909090']);
            $this->assertNull($request->fresh()->company_id);
            $this->assertNull($request->fresh()->subscription_id);
        }
    }

    public function test_non_super_admin_cannot_access_provisioning(): void
    {
        $this->seed();
        $user = User::factory()->create(['role' => 'user']);
        $request = SubscriptionRequest::create([
            'company_name' => 'شركة محمية',
            'applicant_name' => 'مالك',
            'email' => 'protected@example.com',
            'phone' => '0790000000',
            'plan_id' => Plan::firstOrFail()->id,
            'billing_cycle' => 'yearly',
            'status' => SubscriptionRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($user)->get(route('admin.subscription-requests.provision.create', $request))->assertForbidden();
        $this->actingAs($user)->post(route('admin.subscription-requests.provision.store', $request), [])->assertForbidden();
    }

    private function provisionPayload(SubscriptionRequest $request, array $overrides = []): array
    {
        return array_replace([
            'request_id' => (string) $request->id,
            'name_ar' => $request->company_name,
            'name_en' => null,
            'tax_number' => fake()->unique()->numerify('########'),
            'national_number' => null,
            'phone' => $request->phone,
            'email' => $request->email,
            'status' => 'active',
            'jofotara_source_id' => null,
            'jofotara_client_id' => null,
            'jofotara_secret_key' => null,
            'default_language' => 'ar',
            'default_currency' => 'JOD',
            'owner_name' => $request->applicant_name,
            'owner_email' => $request->email,
            'owner_phone' => $request->whatsapp ?: $request->phone,
            'plan_id' => $request->plan_id,
            'billing_cycle' => $request->billing_cycle,
            'feature_keys' => [],
            'admin_notes' => $request->notes,
        ], $overrides);
    }
}
