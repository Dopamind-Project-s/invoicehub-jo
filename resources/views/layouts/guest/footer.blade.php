<footer id="foot">
    <div class="container">
        <div class="row g-5 mb-5">
            <div class="col-lg-5">
                <a class="d-flex align-items-center gap-2 mb-3" href="{{ url('/') }}" style="font-size:1.15rem;font-weight:700;color:var(--tx)">
                    <div class="logo-i"><img src="{{ asset('assets/logos/logo.svg') }}" alt="InvoSync Jo" style="width:50px;height:50px"></div>
                    InvoSync Jo
                </a>
                <p style="font-size:.875rem;color:var(--tx3);line-height:1.65;max-width:320px">{{ data_get($settings ?? [], 'footer.description_ar', 'منصة فوترة إلكترونية عربية للمنشآت.') }}</p>
                <div class="mt-3" style="font-size:.86rem;color:var(--tx3);line-height:1.8">
                    <div>{{ data_get($settings ?? [], 'contact.company_ar', 'دوبامايند') }}</div>
                    <div>{{ data_get($settings ?? [], 'contact.person_ar', 'مصعب الزعبي') }} / {{ data_get($settings ?? [], 'contact.person_en', 'Musab Al-zoubi') }}</div>
                    <a href="mailto:{{ data_get($settings ?? [], 'contact.email', 'musab.m.alzoubii@gmail.com') }}" style="color:var(--pur)">{{ data_get($settings ?? [], 'contact.email', 'musab.m.alzoubii@gmail.com') }}</a>
                </div>
            </div>
            <div class="col-6 col-md-2 fcol"><h5>المنتج</h5><a href="{{ url('/#features') }}">المزايا</a><a href="{{ url('/#integrations') }}">التكاملات</a><a href="{{ url('/#pricing') }}">الباقات</a><a href="{{ url('/#faq') }}">الأسئلة الشائعة</a></div>
            <div class="col-6 col-md-2 fcol"><h5>الحساب</h5><a href="{{ route('login') }}">تسجيل الدخول</a>@if(Route::has('password.request'))<a href="{{ route('password.request') }}">استعادة كلمة المرور</a>@endif</div>
            <div class="col-6 col-md-3 fcol"><h5>معلومات التواصل</h5><a href="mailto:{{ data_get($settings ?? [], 'contact.email', 'musab.m.alzoubii@gmail.com') }}">البريد الإلكتروني</a><a target="_blank" rel="noopener" href="{{ data_get($settings ?? [], 'social.linkedin', 'https://www.linkedin.com/in/musabmalzoubi/') }}">LinkedIn</a><a target="_blank" rel="noopener" href="{{ data_get($settings ?? [], 'social.github', 'https://github.com/MusabAlzoubi') }}">GitHub</a><a target="_blank" rel="noopener" href="{{ data_get($settings ?? [], 'social.facebook', 'https://www.facebook.com/profile.php?id=61562391058375') }}">Facebook</a><a target="_blank" rel="noopener" href="{{ data_get($settings ?? [], 'social.instagram', 'https://www.instagram.com/musab_digitransform') }}">Instagram</a></div>
        </div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pt-4" style="border-top:1px solid var(--bd)">
            <p style="font-size:.8rem;color:var(--tx3);margin:0">© {{ date('Y') }} {{ data_get($settings ?? [], 'footer.copyright_ar', 'جميع الحقوق محفوظة لدوبامايند.') }}</p>
            <div class="d-flex gap-2"><a target="_blank" rel="noopener" href="{{ data_get($settings ?? [], 'social.github', 'https://github.com/MusabAlzoubi') }}" class="sico" aria-label="GitHub"><i class="fa-brands fa-github"></i></a><a target="_blank" rel="noopener" href="{{ data_get($settings ?? [], 'social.linkedin', 'https://www.linkedin.com/in/musabmalzoubi/') }}" class="sico" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a><a target="_blank" rel="noopener" href="{{ data_get($settings ?? [], 'social.facebook', 'https://www.facebook.com/profile.php?id=61562391058375') }}" class="sico" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a><a target="_blank" rel="noopener" href="{{ data_get($settings ?? [], 'social.instagram', 'https://www.instagram.com/musab_digitransform') }}" class="sico" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></div>
        </div>
    </div>
</footer>
