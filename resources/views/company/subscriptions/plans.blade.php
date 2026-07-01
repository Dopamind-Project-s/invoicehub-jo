@extends('layouts.app')
@section('title','الباقات')
@section('content')
@php
    $currentPlan=$subscriptionAccess['plan'];
    $currentRank=(int)($currentPlan?->plan_rank ?? $currentPlan?->sort_order ?? 0);
@endphp
<style>.plans-hero{background:linear-gradient(135deg,#063f52,#00a9c4 58%,#12c2b2);color:#fff;border-radius:28px;padding:28px;box-shadow:0 20px 50px rgba(15,23,42,.14)}.plans-scroll{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(320px,1fr);gap:20px;overflow-x:auto;padding:4px 4px 18px;scroll-snap-type:x mandatory}.pcard{position:relative;border:1px solid #e7f0f4;border-radius:26px;background:#fff;padding:24px;box-shadow:0 14px 36px rgba(15,23,42,.07);scroll-snap-align:start}.pcard.pop{border-color:#12c2b2;box-shadow:0 20px 48px rgba(18,194,178,.18)}.pbadge{position:absolute;top:16px;left:16px;background:linear-gradient(135deg,#00a9c4,#12c2b2);color:#fff;border-radius:999px;padding:6px 12px;font-size:.78rem}.pamt{font-size:2.3rem;font-weight:900;color:#063f52}.pfl{display:flex;align-items:center;gap:9px;padding:8px 0;border-bottom:1px dashed #edf3f6;color:#263d4a}.pchk,.pxx{display:grid;place-items:center;width:22px;height:22px;border-radius:50%;font-weight:900;color:#fff;flex:0 0 22px}.pchk{background:#12c2b2}.pxx{background:#e45858}.current-ribbon{display:inline-flex;align-items:center;gap:6px;background:#e9fbfd;color:#008ba3;border:1px solid #b7edf4;border-radius:999px;padding:7px 12px;font-weight:800}.bgrd{background:linear-gradient(135deg,#00a9c4,#12c2b2);border:0;color:#fff}.bgrd:hover{color:#fff;filter:brightness(.96)}@media(min-width:1200px){.plans-scroll{grid-auto-columns:calc((100% - 40px)/3)}}</style>
<div class="plans-hero mb-4" dir="rtl"><div class="d-flex flex-wrap justify-content-between gap-3 align-items-center"><div><span class="badge bg-white text-primary mb-2">مقارنة الباقات</span><h1 class="h3 mb-2">اختر الباقة الأنسب</h1><p class="mb-0 opacity-75">كل مزايا الموقع الأساسية ظاهرة مع ✓ للموجود و × لغير الموجود، مع تمييز باقتك الحالية.</p></div><a class="btn btn-light rounded-pill px-4" href="{{ route('company.subscriptions.index',$company) }}">رجوع للاشتراك</a></div></div>
<div class="plans-scroll" dir="rtl">
@foreach($plans as $plan)
    @php
        $featureIds=$plan->featureKeys->pluck('id')->all();
        $rank=(int)($plan->plan_rank ?? $plan->sort_order ?? 0);
        $isCurrent=$currentPlan?->id===$plan->id;
        $requestType=$rank>$currentRank?'upgrade':($rank<$currentRank?'downgrade':'renewal');
    @endphp
    <div class="pcard h-100 {{ $plan->is_recommended ? 'pop' : '' }}">
        @if($plan->is_recommended)<span class="pbadge">موصى بها</span>@endif
        @if($isCurrent)<div class="current-ribbon mb-3">✓ مشترك بها حالياً</div>@endif
        <h3 class="fw-bold">{{ $plan->name_ar ?: $plan->name }}</h3>
        <div class="pamt mb-1"><span>{{ number_format((float)$plan->monthly_price,3) }}</span><sup class="fs-6">د.أ</sup></div>
        <div class="text-muted small">شهرياً / {{ number_format((float)$plan->yearly_price,3) }} د.أ سنوياً</div>
        <p class="text-muted my-3 pb-3 border-bottom">{{ $plan->description_ar ?: $plan->description }}</p>
        @foreach($allFeatures as $feature)
            @php($has=in_array($feature->id,$featureIds,true))
            <div class="pfl"><span class="{{ $has ? 'pchk' : 'pxx' }}">{{ $has ? '✓' : '×' }}</span>{{ $feature->name_ar ?: $feature->name }}</div>
        @endforeach
        <form method="post" action="{{ route('company.subscriptions.requests.store',$company) }}" class="mt-4">@csrf<input type="hidden" name="request_type" value="{{ $requestType }}"><input type="hidden" name="requested_plan_id" value="{{ $plan->id }}"><input type="hidden" name="billing_cycle" value="monthly"><button class="btn {{ $isCurrent ? 'btn-outline-secondary' : 'bgrd' }} w-100 py-2 rounded-pill" @disabled($isCurrent)>{{ $isCurrent ? 'الباقة الحالية' : ($requestType==='upgrade' ? 'طلب ترقية' : ($requestType==='downgrade' ? 'طلب تخفيض' : 'طلب تجديد')) }}</button></form>
    </div>
@endforeach
</div>
@endsection
