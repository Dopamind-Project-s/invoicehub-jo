@props(['items' => []])
<style>
.subscription-timeline{position:relative;display:grid;gap:12px}.subscription-step{display:flex;gap:12px;align-items:flex-start}.subscription-step-dot{width:16px;height:16px;border-radius:999px;margin-top:5px;background:#cbd5e1;box-shadow:0 0 0 4px #f1f5f9}.subscription-step.done .subscription-step-dot{background:#16a34a}.subscription-step.active .subscription-step-dot{background:#00a9c4}.subscription-step.warning .subscription-step-dot{background:#f59e0b}.subscription-step.danger .subscription-step-dot{background:#dc2626}.subscription-step-title{font-weight:800;color:#172033}.subscription-step-value{color:#64748b;font-size:.9rem}.payment-pill{border:1px solid #d7eef3;border-radius:999px;padding:8px 12px;background:#f8fdff;font-weight:800;color:#0f6170}.preview-chip{border:1px solid #e5eef4;border-radius:14px;padding:8px 10px;background:#fff}.loading-skeleton{min-height:12px;border-radius:999px;background:linear-gradient(90deg,#eef2f7,#f8fafc,#eef2f7);background-size:200% 100%;animation:skeleton 1.4s infinite}@keyframes skeleton{to{background-position:-200% 0}}
</style>
<div class="subscription-timeline">
    @forelse($items as $item)
        <div class="subscription-step {{ $item['state'] ?? 'muted' }}">
            <span class="subscription-step-dot"></span>
            <div><div class="subscription-step-title">{{ $item['label'] }}</div><div class="subscription-step-value">{{ $item['value'] }}</div></div>
        </div>
    @empty
        <div class="text-muted">لا توجد بيانات Timeline.</div>
    @endforelse
</div>
