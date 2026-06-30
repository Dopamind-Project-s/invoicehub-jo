<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FeatureKey;
use App\Models\Plan;
use App\Models\SubscriptionChangeRequest;
use App\Services\Subscriptions\SubscriptionAccessService;
use App\Services\Subscriptions\SubscriptionEventLogger;
use App\Services\Subscriptions\SubscriptionPresentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function index(Company $company, SubscriptionAccessService $subscriptions, SubscriptionPresentationService $presenter)
    {
        $company->load(['subscriptions.plan.featureKeys']);
        $access = $subscriptions->resolve($company);
        $plans = $this->activePlans();

        return view('company.subscriptions.index', [
            'company' => $company,
            'subscriptionAccess' => $access,
            'plans' => $plans,
            'history' => $company->subscriptions()->with('plan')->latest('current_period_start_at')->latest('id')->get(),
            'requests' => SubscriptionChangeRequest::with('requestedPlan')->where('company_id', $company->id)->latest()->get(),
            'events' => \App\Models\SubscriptionEvent::where('company_id', $company->id)->latest('occurred_at')->limit(20)->get(),
            'timeline' => $presenter->timeline($access['subscription'], $access['effective_status']),
            'health' => $presenter->health($access['subscription'], $access['effective_status']),
            'renewalSummary' => $presenter->renewalSummary($access['subscription']),
            'paymentMethods' => $presenter->paymentMethods(),
            'allFeatures' => $this->activeFeatures(),
        ]);
    }

    public function plans(Company $company, SubscriptionAccessService $subscriptions)
    {
        $company->load(['subscriptions.plan.featureKeys']);
        $access = $subscriptions->resolve($company);

        return view('company.subscriptions.plans', [
            'company' => $company,
            'subscriptionAccess' => $access,
            'plans' => $this->activePlans(),
            'allFeatures' => $this->activeFeatures(),
        ]);
    }

    private function activePlans()
    {
        return Plan::where('is_active', true)
            ->with('featureKeys')
            ->orderBy('sort_order')
            ->orderBy('plan_rank')
            ->orderBy('name')
            ->get();
    }

    private function activeFeatures()
    {
        return FeatureKey::where('is_active', true)->orderBy('category')->orderBy('code')->get();
    }

    public function requestChange(Request $request, Company $company, SubscriptionAccessService $subscriptions, SubscriptionEventLogger $events): RedirectResponse
    {
        $data = $request->validate([
            'request_type' => ['required', Rule::in(['upgrade', 'downgrade', 'renewal', 'cancel'])],
            'requested_plan_id' => ['nullable', 'required_unless:request_type,renewal,cancel', 'exists:plans,id'],
            'billing_cycle' => ['nullable', Rule::in(['monthly', 'yearly', 'manual', 'trial'])],
            'requested_effective_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $subscription = $subscriptions->currentSubscription($company);
        $changeRequest = SubscriptionChangeRequest::create($data + [
            'company_id' => $company->id,
            'current_subscription_id' => $subscription?->id,
            'status' => 'pending',
            'requested_by' => $request->user()->id,
        ]);
        $events->record($company, $subscription, 'change_requested', 'customer', $request->user(), ['request_id' => $changeRequest->id, 'request_type' => $changeRequest->request_type]);

        return back()->with('success', 'تم إرسال طلب الاشتراك للمراجعة. لن يتم تنفيذ أي تغيير قبل موافقة الإدارة.');
    }
}
