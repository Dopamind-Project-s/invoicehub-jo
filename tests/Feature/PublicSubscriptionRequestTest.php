<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\SubscriptionRequest;
use App\Models\User;
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
}
