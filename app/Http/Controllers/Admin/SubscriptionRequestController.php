<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvisionSubscriptionRequestRequest;
use App\Models\Company;
use App\Models\FeatureKey;
use App\Models\Plan;
use App\Models\SubscriptionRequest;
use App\Services\Admin\SubscriptionRequestProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class SubscriptionRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = SubscriptionRequest::query()
            ->with(['plan', 'approvedBy', 'company', 'subscription'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('plan_id'), fn ($query) => $query->where('plan_id', $request->integer('plan_id')))
            ->when($request->filled('billing_cycle'), fn ($query) => $query->where('billing_cycle', $request->string('billing_cycle')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.subscription-requests.index', [
            'requests' => $requests,
            'plans' => Plan::orderBy('sort_order')->orderBy('name')->get(),
            'statuses' => SubscriptionRequest::statuses(),
        ]);
    }

    public function show(SubscriptionRequest $subscriptionRequest): View
    {
        $subscriptionRequest->load(['plan', 'approvedBy', 'company', 'user', 'subscription.plan', 'provisionedBy']);

        return view('admin.subscription-requests.show', ['subscriptionRequest' => $subscriptionRequest]);
    }

    public function update(Request $request, SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([SubscriptionRequest::STATUS_PENDING, SubscriptionRequest::STATUS_CONTACTED, SubscriptionRequest::STATUS_APPROVED, SubscriptionRequest::STATUS_REJECTED])],
            'admin_notes' => ['nullable', 'string', 'max:4000'],
        ]);

        if ($subscriptionRequest->isProvisioned()) {
            return back()->withErrors(['status' => 'لا يمكن تغيير حالة طلب تم تجهيزه.']);
        }

        $payload = ['status' => $data['status'], 'admin_notes' => $data['admin_notes'] ?? $subscriptionRequest->admin_notes];
        if ($data['status'] === SubscriptionRequest::STATUS_APPROVED && $subscriptionRequest->status !== SubscriptionRequest::STATUS_APPROVED) {
            $payload['approved_at'] = now();
            $payload['approved_by'] = $request->user()->id;
        }

        $subscriptionRequest->update($payload);

        return redirect()->route('admin.subscription-requests.show', $subscriptionRequest)->with('success', 'تم تحديث طلب الاشتراك.');
    }

    public function provisionCreate(SubscriptionRequest $subscriptionRequest): View|RedirectResponse
    {
        $subscriptionRequest->load(['plan', 'company', 'subscription']);

        if ($subscriptionRequest->isProvisioned()) {
            return redirect()->route('admin.subscription-requests.show', $subscriptionRequest)->with('status', 'تم تجهيز هذا الطلب مسبقًا.');
        }

        abort_if($subscriptionRequest->status === SubscriptionRequest::STATUS_REJECTED, 422, 'لا يمكن تجهيز طلب مرفوض.');

        return view('admin.subscription-requests.provision', [
            'subscriptionRequest' => $subscriptionRequest,
            'company' => new Company([
                'name_ar' => $subscriptionRequest->company_name,
                'phone' => $subscriptionRequest->phone,
                'email' => $subscriptionRequest->email,
                'status' => 'active',
                'is_active' => true,
                'default_language' => 'ar',
                'default_currency' => 'JOD',
                'country_code' => 'JO',
                'icv_prefix' => 'INV',
            ]),
            'plans' => Plan::where('is_active', true)->with('featureKeys')->orderBy('sort_order')->orderBy('name')->get(),
            'features' => FeatureKey::where('is_active', true)->orderBy('category')->orderBy('code')->get(),
            'selectedPlanId' => $subscriptionRequest->plan_id,
            'selectedBillingCycle' => $subscriptionRequest->billing_cycle,
        ]);
    }

    public function provisionStore(ProvisionSubscriptionRequestRequest $request, SubscriptionRequest $subscriptionRequest, SubscriptionRequestProvisioningService $provisioning): RedirectResponse
    {
        try {
            $subscriptionRequest = $provisioning->provision($subscriptionRequest->load('plan'), $request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['provision' => $exception->getMessage()]);
        }

        return redirect()->route('admin.subscription-requests.show', $subscriptionRequest)->with('success', 'تم تجهيز المنشأة والاشتراك بنجاح.');
    }
}
