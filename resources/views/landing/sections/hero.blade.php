<section id="hero" dir="rtl">
    <div class="aur aur-a" style="top:-80px;left:-120px"></div><div class="aur aur-b" style="top:180px;right:-180px"></div>
    <div class="container position-relative" style="z-index:2">
        <div class="text-center">
            <span class="hbadge"><span class="bdot"></span>نظام فوترة إلكترونية للمنشآت في الأردن</span>
            <h1 class="h1 mt-4">أدر فواتير منشأتك بثقة مع <span class="gt">InvoSync</span></h1>
            <p class="mx-auto" style="max-width:720px;font-size:clamp(1rem,1.8vw,1.25rem);color:var(--tx2);margin-bottom:36px">منصة عربية لإدارة الفواتير والعملاء والمنتجات، وتجهيز بيانات منشأتك للربط مع نظام الفوترة الوطني بخطوات واضحة وتجربة سهلة.</p>
            <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                <a href="{{ route('login') }}" class="bgrd btn px-4 py-3 fs-6">{{ data_get($settings, 'cta.primary_text_ar', 'ابدأ إدارة فواتير منشأتك') }}</a>
                <a href="https://wa.me/962{{ ltrim(data_get($settings, 'contact.whatsapp', '0776079926'), '0') }}" class="boc btn px-4 py-3 fs-6">{{ data_get($settings, 'cta.secondary_text_ar', 'تواصل عبر واتساب') }}</a>
            </div>
        </div>
        <div class="row justify-content-center mt-5"><div class="col-lg-10"><div class="dwrap"><div class="dtbar"><span class="dd" style="background:#00a9c4"></span><span class="dd" style="background:#12c2b2"></span><span class="dd" style="background:#28c840"></span><span class="ms-auto me-auto" style="font-size:.8rem;color:var(--tx3)">لوحة تحكم InvoSync</span></div><div class="row g-3 p-4"><div class="col-md-4"><div class="stpill"><strong class="gt">الفواتير</strong><br><span>إنشاء، اعتماد، وتصدير PDF</span></div></div><div class="col-md-4"><div class="stpill"><strong>العملاء والمنتجات</strong><br><span>بيانات منظمة لكل منشأة</span></div></div><div class="col-md-4"><div class="stpill"><strong>الفوترة الوطنية</strong><br><span>تهيئة وربط جوفوتارا</span></div></div></div></div></div></div>
    </div>
</section>
