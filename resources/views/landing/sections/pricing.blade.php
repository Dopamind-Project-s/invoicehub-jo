<section id="pricing" class="sp" style="background:var(--bg2)" dir="rtl">
    <div class="container">
        <div class="text-center mb-5">
            <span class="slbl">الباقات</span>
            <h2 class="stitle">باقات واضحة قابلة للتوسع</h2>
            <p class="ssub mx-auto">تُقرأ الباقات مباشرة من لوحة الإدارة مع مفاتيح المزايا المرتبطة.</p>
        </div>

        @if(count($plans) > 0)
            <div class="d-flex align-items-center justify-content-center gap-3 mb-5 rv in">
                <span style="font-size:.9rem;color:var(--tx2);font-weight:500">سنويا</span>
                <div style="position:relative;width:52px;height:28px">
                    <input type="checkbox" id="ptog" style="position:absolute;opacity:0;width:0;height:0" aria-label="تبديل عرض الأسعار بين الشهري والسنوي">
                    <label for="ptog" id="ptogLabel" style="position:absolute;inset:0;background:var(--sf);border:1px solid var(--bd2);border-radius:100px;cursor:pointer;transition:.3s">
                        <span id="ptogThumb" style="position:absolute;width:20px;height:20px;left:3px;top:3px;background:var(--grad);border-radius:50%;transition:.3s;display:block;transform:translateX(0)"></span>
                    </label>
                </div>
                <span style="font-size:.9rem;color:var(--tx2);font-weight:500">شهرياً</span>
                <span style="background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.25);color:#34d399;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:100px"><i class="fa-solid fa-tag me-1"></i>وفر 30%</span>
            </div>
        @endif

        <div class="row g-4 align-items-stretch">
            @forelse($plans as $plan)
                @php
                    $monthlyPrice = number_format((float) data_get($plan, 'monthly_price', 0), 3);
                    $yearlyPrice = number_format((float) data_get($plan, 'yearly_price', 0), 3);
                @endphp
                <div class="col-md-4">
                    <div class="pcard h-100 {{ data_get($plan, 'is_recommended') ? 'pop' : '' }}">
                        @if(data_get($plan, 'is_recommended'))
                            <span class="pbadge">موصى بها</span>
                        @endif
                        <h3>{{ data_get($plan, 'name_ar') ?: data_get($plan, 'name') }}</h3>
                        <div class="pamt mb-1"><span class="pv" data-m="{{ $monthlyPrice }}" data-y="{{ $yearlyPrice }}">{{ $monthlyPrice }}</span><sup>د.أ</sup></div>
                        <div class="pper" style="font-size:.82rem;color:var(--tx3)" data-monthly-label="شهرياً" data-yearly-label="سنوياً">شهرياً</div>
                        <p style="font-size:.875rem;color:var(--tx2);margin:14px 0 20px;padding-bottom:20px;border-bottom:1px solid var(--bd)">{{ data_get($plan, 'description_ar') ?: data_get($plan, 'description') }}</p>
                        @foreach(data_get($plan, 'features', []) as $feature)
                            <div class="pfl"><span class="pchk">✓</span>{{ data_get($feature, 'name_ar') ?: data_get($feature, 'name') }}</div>
                        @endforeach
                        <a class="bgrd btn w-100 py-2 mt-4" href="{{ route('login') }}">اختر الباقة</a>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="gc p-4 text-center">لا توجد باقات فعالة حالياً.</div></div>
            @endforelse
        </div>
    </div>
</section>
