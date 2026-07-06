<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SubscriptionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = SubscriptionRequest::query()
            ->with(['plan', 'approvedBy'])
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
        $subscriptionRequest->load(['plan', 'approvedBy']);

        return view('admin.subscription-requests.show', ['subscriptionRequest' => $subscriptionRequest]);
    }

    public function update(Request $request, SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(SubscriptionRequest::statuses())],
            'admin_notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $payload = ['status' => $data['status'], 'admin_notes' => $data['admin_notes'] ?? null];
        if ($data['status'] === SubscriptionRequest::STATUS_APPROVED && $subscriptionRequest->status !== SubscriptionRequest::STATUS_APPROVED) {
            $payload['approved_at'] = now();
            $payload['approved_by'] = $request->user()->id;
            // TODO: Wire this approval to the tenant/company/user/subscription provisioning service when the automated onboarding flow is finalized.
        }

        $subscriptionRequest->update($payload);

        return redirect()->route('admin.subscription-requests.show', $subscriptionRequest)->with('success', 'تم تحديث طلب الاشتراك.');
    }
}
