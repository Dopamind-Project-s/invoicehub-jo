<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequestRequest;
use App\Models\Plan;
use App\Models\SubscriptionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionRequestController extends Controller
{
    public function create(Request $request): View
    {
        $plan = Plan::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->where('is_public', true)->orWhereNull('is_public');
            })
            ->when($request->query('plan'), fn ($query, $plan) => is_numeric($plan) ? $query->whereKey($plan) : $query->where('slug', $plan))
            ->firstOrFail();

        return view('subscription-requests.create', ['plan' => $plan]);
    }

    public function store(StoreSubscriptionRequestRequest $request): RedirectResponse
    {
        SubscriptionRequest::create($request->validated() + ['status' => SubscriptionRequest::STATUS_PENDING]);

        return redirect()->route('subscription-requests.thank-you');
    }

    public function thankYou(): View
    {
        return view('subscription-requests.thank-you');
    }
}
