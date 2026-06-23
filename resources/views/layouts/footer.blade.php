  <!-- FOOTER -->
         <footer id="foot">
            <div class="container">
               <div class="row g-5 mb-5">
                  <div class="col-lg-4">
                     <a class="d-flex align-items-center gap-2 mb-3" href="#" style="font-size:1.15rem;font-weight:700;color:var(--tx)">
                        <div class="logo-i"><img src="{{ asset('assets/logos/logo.svg') }}" alt="InvoSync Jo" style="width:50px;height:50px"></div>
                        InvoSync Jo
                     </a>
                     <p style="font-size:.875rem;color:var(--tx3);line-height:1.65;max-width:280px">{{ data_get($settings ?? [], 'footer.description_ar', 'منصة فوترة إلكترونية عربية للمنشآت.') }}</p>
                     <div class="d-flex gap-2 mt-3"><input class="nli" type="email" placeholder="your@email.com" style="max-width:200px"><button class="bgrd btn px-3 py-2" style="font-size:.85rem;white-space:nowrap">Subscribe</button></div>
                  </div>
                  <div class="col-6 col-md-2 fcol">
                     <h5>Product</h5>
                     <a href="#">Features</a><a href="#">Integrations</a><a href="#">Pricing</a><a href="#">Changelog</a><a href="#">Status</a>
                  </div>
                  <div class="col-6 col-md-2 fcol">
                     <h5>Resources</h5>
                     <a href="#">Documentation</a><a href="#">API Reference</a><a href="#">Blog</a><a href="#">Case Studies</a><a href="#">Community</a>
                  </div>
                  <div class="col-6 col-md-2 fcol">
                     <h5>Company</h5>
                     <a href="#">About</a><a href="#">Careers</a><a href="#">Press</a><a href="#">Privacy</a><a href="#">Terms</a>
                  </div>
               </div>
               <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pt-4" style="border-top:1px solid var(--bd)">
                  <p style="font-size:.8rem;color:var(--tx3);margin:0">© {{ date('Y') }} {{ data_get($settings ?? [], 'footer.copyright_ar', 'جميع الحقوق محفوظة.') }} <a target="_blank" class="text-primary fw-bold" href="https://bestwpware.com/">Bestwpware</a> <br> Distributed by <a target="_blank" class="text-primary fw-bold" href="https://themewagon.com">ThemeWagon</a> </p>
                  <div class="d-flex gap-2"><a href="#" class="sico"><i class="fa-brands fa-x-twitter"></i></a><a href="#" class="sico"><i class="fa-brands fa-github"></i></a><a href="#" class="sico"><i class="fa-brands fa-linkedin-in"></i></a><a href="#" class="sico"><i class="fa-brands fa-discord"></i></a></div>
               </div>
            </div>
         </footer>
      </div>
      <!-- /landing -->
      <!-- ======================== LOGIN OFFCANVAS ======================== -->
      <div class="offcanvas offcanvas-end" tabindex="-1" id="lofc">
         <div class="offcanvas-header">
            <div class="d-flex align-items-center gap-2">
               <div class="logo-i" style="width:30px;height:30px;font-size:.8rem"><img src="{{ asset('assets/logos/logo.svg') }}" alt="InvoSync Jo" style="width:50px;height:50px"></div>
               <h5 class="offcanvas-title mb-0">InvoSync Jo</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" style="filter:invert(1)"></button>
         </div>
         <div class="offcanvas-body p-4">
            <div class="tab-switch"><button class="tab-sw-btn on" id="tabLogin" onclick="swTab('login')">Log In</button><button class="tab-sw-btn" id="tabSignup" onclick="swTab('signup')">Sign Up</button></div>
            <!-- Login -->
            <div id="fLogin">
               <x-auth-session-status class="alert alert-success" :status="session('status')" />
               <form method="POST" action="{{ route('login') }}" class="d-grid gap-3">
                  @csrf
                  <div>
                     <label class="olbl" for="landingLoginEmail"><i class="fa-regular fa-envelope me-1"></i>Email address</label>
                     <input class="oinp" type="email" id="landingLoginEmail" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autocomplete="username">
                     <x-input-error :messages="$errors->get('email')" class="text-danger small mt-2" />
                  </div>
                  <div>
                     <label class="olbl" for="landingLoginPassword"><i class="fa-solid fa-lock me-1"></i>Password</label>
                     <input class="oinp" type="password" id="landingLoginPassword" name="password" placeholder="********" required autocomplete="current-password">
                     <x-input-error :messages="$errors->get('password')" class="text-danger small mt-2" />
                  </div>
                  <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                     <label class="form-check m-0">
                        <input class="form-check-input" type="checkbox" name="remember">
                        <span class="form-check-label">Remember me</span>
                     </label>
                     @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="font-size:.8rem;color:var(--pur)">Forgot password?</a>
                     @endif
                  </div>
                  <button type="submit" class="bgrd btn w-100 py-3 fw-semibold fs-6" id="loginBtn">Log In <i class="fa-solid fa-arrow-right ms-1 fa-sm"></i></button>
               </form>
               @if (Route::has('register'))
                  <p class="text-center mt-4" style="font-size:.82rem;color:var(--tx3)">Don't have an account? <a href="{{ route('register') }}" style="color:var(--pur)">Sign up free</a></p>
               @endif
            </div>
            <!-- Sign Up -->
            <div id="fSignup" style="display:none">
               @if (Route::has('register'))
                  <p class="mb-3" style="color:var(--tx2)">Create accounts through the secure Laravel registration page.</p>
                  <a class="bgrd btn w-100 py-3 fw-semibold fs-6" href="{{ route('register') }}">Create Free Account <i class="fa-solid fa-arrow-right ms-1 fa-sm"></i></a>
               @else
                  <p class="mb-0" style="color:var(--tx2)">Registration is currently disabled. Please contact the administrator.</p>
               @endif
            </div>
         </div>
      </div>
      <!-- ======================== DASHBOARD ======================== -->
      <div id="dashboard">
         <!-- Sidebar -->
         <div class="db-sidebar" id="dbSidebar">
            <div class="db-logo">
               <div class="logo-i"><img src="{{ asset('assets/logos/logo.svg') }}" alt="InvoSync Jo" style="width:50px;height:50px"></div>
               <span>InvoSync Jo</span>
            </div>
            <div class="db-nav">
               <div class="db-nav-section">Main</div>
               <button class="db-nl active" onclick="dbNav('overview',this)"><i class="fa-solid fa-gauge-high"></i> Overview</button>
               <button class="db-nl" onclick="dbNav('agents',this)"><i class="fa-solid fa-robot"></i> AI Agents <span class="db-badge">4</span></button>
               <button class="db-nl" onclick="dbNav('chat',this)"><i class="fa-regular fa-comments"></i> AI Chat <span class="db-badge" style="background:#34d399">Live</span></button>
               <button class="db-nl" onclick="dbNav('analytics',this)"><i class="fa-solid fa-chart-line"></i> Analytics</button>
               <button class="db-nl" onclick="dbNav('automations',this)"><i class="fa-solid fa-bolt"></i> Automations</button>
               <div class="db-nav-section">Workspace</div>
               <button class="db-nl" onclick="dbNav('integrations',this)"><i class="fa-solid fa-plug"></i> Integrations</button>
               <button class="db-nl" onclick="dbNav('settings',this)"><i class="fa-solid fa-gear"></i> Settings</button>
            </div>
            <div class="db-bottom">
               <button class="db-nl" onclick="doLogout()" style="color:#f87171"><i class="fa-solid fa-right-from-bracket"></i> Log Out</button>
            </div>
         </div>
         <!-- Top bar -->
         <div class="db-top">
            <button class="boc d-lg-none me-2 px-2 py-2" style="border-radius:10px;width:38px;height:38px" onclick="document.getElementById('dbSidebar').classList.toggle('mob-open')">
            <i class="fa-solid fa-bars"></i>
            </button>
            <div class="db-top-search">
               <i class="fa-solid fa-magnifying-glass"></i>
               <input type="text" placeholder="Search agents, conversations...">
            </div>
            <div class="ms-auto d-flex align-items-center gap-3">
               <button class="boc d-flex align-items-center justify-content-center" id="dbThBtn" style="width:38px;height:38px;padding:0;border-radius:12px" onclick="toggleTheme()">
               <i class="fa-solid fa-sun" id="dbSunI" style="display:none"></i>
               <i class="fa-solid fa-moon" id="dbMoonI"></i>
               </button>
               <div style="position:relative" id="notifWrap">
                  <button class="boc d-flex align-items-center justify-content-center" id="bellBtn" style="width:38px;height:38px;padding:0;border-radius:12px" onclick="toggleNotif(event)">
                  <i class="fa-regular fa-bell"></i>
                  </button>
                  <span id="notifBadge" style="position:absolute;top:5px;right:5px;width:9px;height:9px;border-radius:50%;background:#f87171;border:2px solid var(--bg)"></span>
                  <!-- Notification Dropdown -->
                  <div class="db-dropdown" id="notifDropdown" style="right:0;min-width:360px">
                     <div class="dd-header">
                        <div class="dd-header-title"><i class="fa-regular fa-bell me-2" style="color:var(--pur)"></i>Notifications <span id="unreadCount" style="background:rgba(248,113,113,.15);color:#f87171;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:100px;margin-left:6px">4 new</span></div>
                        <div class="d-flex gap-2">
                           <button class="dd-close" onclick="markAllRead()" title="Mark all read" style="width:auto;padding:4px 10px;font-size:.72rem;color:var(--pur);border-color:rgba(14,165,233,.3)">Mark all read</button>
                           <button class="dd-close" onclick="toggleNotif(event)"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                     </div>
                     <div class="dd-body" id="notifList">
                        <div class="notif-item notif-unread d-flex gap-3 mb-2">
                           <div class="notif-ico" style="background:rgba(248,113,113,.12)"><i class="fa-solid fa-triangle-exclamation" style="color:#f87171;font-size:.9rem"></i></div>
                           <div style="flex:1">
                              <div style="font-size:.85rem;font-weight:600;margin-bottom:3px">High ticket volume alert</div>
                              <div style="font-size:.78rem;color:var(--tx2)">Support Bot handling 23% above average. Consider scaling agents.</div>
                              <div style="font-size:.7rem;color:var(--tx3);margin-top:5px"><i class="fa-regular fa-clock me-1"></i>2 minutes ago</div>
                           </div>
                           <div class="notif-dot"></div>
                        </div>
                        <div class="notif-item notif-unread d-flex gap-3 mb-2">
                           <div class="notif-ico" style="background:rgba(52,211,153,.12)"><i class="fa-solid fa-chart-line" style="color:#34d399;font-size:.9rem"></i></div>
                           <div style="flex:1">
                              <div style="font-size:.85rem;font-weight:600;margin-bottom:3px">Daily goal reached ??</div>
                              <div style="font-size:.78rem;color:var(--tx2)">Your AI agents resolved 1,000 conversations today � a new record!</div>
                              <div style="font-size:.7rem;color:var(--tx3);margin-top:5px"><i class="fa-regular fa-clock me-1"></i>18 minutes ago</div>
                           </div>
                           <div class="notif-dot"></div>
                        </div>
                        <div class="notif-item notif-unread d-flex gap-3 mb-2">
                           <div class="notif-ico" style="background:rgba(14,165,233,.12)"><i class="fa-brands fa-slack" style="color:#38bdf8;font-size:.9rem"></i></div>
                           <div style="flex:1">
                              <div style="font-size:.85rem;font-weight:600;margin-bottom:3px">Slack sync completed</div>
                              <div style="font-size:.78rem;color:var(--tx2)">#support channel successfully synced. 47 new threads indexed for AI training.</div>
                              <div style="font-size:.7rem;color:var(--tx3);margin-top:5px"><i class="fa-regular fa-clock me-1"></i>1 hour ago</div>
                           </div>
                           <div class="notif-dot"></div>
                        </div>
                        <div class="notif-item notif-unread d-flex gap-3 mb-2">
                           <div class="notif-ico" style="background:rgba(245,158,11,.1)"><i class="fa-solid fa-robot" style="color:#fbbf24;font-size:.9rem"></i></div>
                           <div style="flex:1">
                              <div style="font-size:.85rem;font-weight:600;margin-bottom:3px">Agent update available</div>
                              <div style="font-size:.78rem;color:var(--tx2)">Support Bot v2.2 is ready. Improved accuracy by 4.3% on billing queries.</div>
                              <div style="font-size:.7rem;color:var(--tx3);margin-top:5px"><i class="fa-regular fa-clock me-1"></i>3 hours ago</div>
                           </div>
                           <div class="notif-dot"></div>
                        </div>
                        <div class="notif-item d-flex gap-3 mb-2">
                           <div class="notif-ico" style="background:rgba(6,182,212,.1)"><i class="fa-solid fa-file-lines" style="color:#60a5fa;font-size:.9rem"></i></div>
                           <div style="flex:1">
                              <div style="font-size:.85rem;font-weight:600;margin-bottom:3px">Weekly report ready</div>
                              <div style="font-size:.78rem;color:var(--tx2)">Your performance report for last week is ready to download.</div>
                              <div style="font-size:.7rem;color:var(--tx3);margin-top:5px"><i class="fa-regular fa-clock me-1"></i>Yesterday</div>
                           </div>
                           <div class="notif-dot read"></div>
                        </div>
                        <div class="notif-item d-flex gap-3 mb-2">
                           <div class="notif-ico" style="background:rgba(52,211,153,.1)"><i class="fa-solid fa-plug" style="color:#34d399;font-size:.9rem"></i></div>
                           <div style="flex:1">
                              <div style="font-size:.85rem;font-weight:600;margin-bottom:3px">GitHub integration connected</div>
                              <div style="font-size:.78rem;color:var(--tx2)">Successfully connected 2 repositories. AI code review is now active.</div>
                              <div style="font-size:.7rem;color:var(--tx3);margin-top:5px"><i class="fa-regular fa-clock me-1"></i>2 days ago</div>
                           </div>
                           <div class="notif-dot read"></div>
                        </div>
                     </div>
                     <div style="padding:12px 14px;border-top:1px solid var(--bd);text-align:center">
                        <button class="boc btn w-100 py-2" style="font-size:.82rem;border-radius:10px"><i class="fa-solid fa-list me-1"></i>View All Notifications</button>
                     </div>
                  </div>
               </div>
               <div style="position:relative" id="profileWrap">
                  <div class="db-user-pill" id="userPill" onclick="toggleProfile(event)">
                     <div class="db-avatar" id="userAvatar">U</div>
                     <div class="d-none d-md-block">
                        <div style="font-size:.85rem;font-weight:600;line-height:1.2" id="userName">User</div>
                        <div style="font-size:.72rem;color:var(--tx3)" id="userPlan">Pro Plan</div>
                     </div>
                     <i class="fa-solid fa-chevron-down fa-xs" id="profileChevron" style="color:var(--tx3);margin-left:2px;transition:.3s"></i>
                  </div>
                  <!-- Profile Dropdown -->
                  <div class="db-dropdown profile-dd" id="profileDropdown" style="right:0">
                     <div class="profile-dd-top">
                        <div class="db-avatar" id="pdAvatar" style="width:46px;height:46px;border-radius:14px;font-size:1rem;flex-shrink:0">U</div>
                        <div>
                           <div style="font-weight:700;font-size:.95rem" id="pdName">User</div>
                           <div style="font-size:.78rem;color:var(--tx3)" id="pdEmail">user@email.com</div>
                           <div style="margin-top:5px"><span class="bst son" style="font-size:.68rem" id="pdPlan">Pro Plan</span></div>
                        </div>
                     </div>
                     <div style="padding:8px 0">
                        <button class="profile-menu-item" onclick="dbNav('settings',document.querySelector('[onclick*=settings]'));toggleProfile()"><i class="fa-regular fa-user" style="color:var(--pur)"></i>My Profile</button>
                        <button class="profile-menu-item" onclick="dbNav('settings',document.querySelector('[onclick*=settings]'));toggleProfile()"><i class="fa-solid fa-gear" style="color:var(--tx3)"></i>Account Settings</button>
                        <button class="profile-menu-item" onclick="dbNav('integrations',document.querySelector('[onclick*=integrations]'));toggleProfile()"><i class="fa-solid fa-plug" style="color:#60a5fa"></i>Integrations</button>
                        <button class="profile-menu-item" onclick="dbNav('chat',document.querySelector('[onclick*=chat]'));toggleProfile()"><i class="fa-solid fa-robot" style="color:#34d399"></i>AI Chat Console</button>
                        <div style="height:1px;background:var(--bd);margin:8px 16px"></div>
                        <div style="padding:8px 16px 4px">
                           <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--tx3);margin-bottom:8px">Current Plan</div>
                           <div style="background:linear-gradient(135deg,rgba(14,165,233,.12),rgba(6,182,212,.08));border:1px solid rgba(14,165,233,.25);border-radius:12px;padding:12px">
                              <div style="font-weight:700;font-size:.88rem;margin-bottom:3px" id="pdPlanDetail">Pro Plan</div>
                              <div style="font-size:.76rem;color:var(--tx2);margin-bottom:8px">Next billing: June 1, 2025</div>
                              <button class="bgrd btn w-100 py-1" style="font-size:.75rem;border-radius:8px">Upgrade to Enterprise</button>
                           </div>
                        </div>
                        <div style="height:1px;background:var(--bd);margin:8px 16px"></div>
                        <button class="profile-menu-item danger" onclick="doLogout();toggleProfile()"><i class="fa-solid fa-right-from-bracket" style="color:#f87171"></i>Log Out</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- Main Content -->
         <div class="db-main">
            <div class="db-content">
               <!-- -- OVERVIEW -- -->
               <div class="db-section active" id="sec-overview">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                     <div>
                        <h4 class="fw-700 mb-1" style="font-size:1.4rem;font-weight:700">Good morning, <span id="greetName">User</span> ??</h4>
                        <p style="font-size:.875rem;color:var(--tx3);margin:0">Here's what's happening with your AI agents today.</p>
                     </div>
                     <button class="bgrd btn px-3 py-2" style="font-size:.85rem;white-space:nowrap" onclick="dbNav('chat',document.querySelector('[onclick*=chat]'))"><i class="fa-solid fa-robot me-2"></i>Ask AI Agent</button>
                  </div>
                  <!-- Stat cards -->
                  <div class="row g-3 mb-4">
                     <div class="col-6 col-xl-3">
                        <div class="db-stat-card">
                           <div class="d-flex justify-content-between align-items-start mb-3">
                              <div style="width:38px;height:38px;border-radius:11px;background:rgba(14,165,233,.15);display:flex;align-items:center;justify-content:center"><i class="fa-regular fa-comments" style="color:#38bdf8"></i></div>
                              <span style="font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:100px;background:rgba(52,211,153,.1);color:#34d399">? 18.4%</span>
                           </div>
                           <div class="db-stat-val gt">24.8K</div>
                           <div class="db-stat-lbl">Total Conversations</div>
                        </div>
                     </div>
                     <div class="col-6 col-xl-3">
                        <div class="db-stat-card">
                           <div class="d-flex justify-content-between align-items-start mb-3">
                              <div style="width:38px;height:38px;border-radius:11px;background:rgba(52,211,153,.12);display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-bullseye" style="color:#34d399"></i></div>
                              <span style="font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:100px;background:rgba(52,211,153,.1);color:#34d399">? 3.1%</span>
                           </div>
                           <div class="db-stat-val" style="color:#34d399">98.2%</div>
                           <div class="db-stat-lbl">Resolution Rate</div>
                        </div>
                     </div>
                     <div class="col-6 col-xl-3">
                        <div class="db-stat-card">
                           <div class="d-flex justify-content-between align-items-start mb-3">
                              <div style="width:38px;height:38px;border-radius:11px;background:rgba(6,182,212,.12);display:flex;align-items:center;justify-content:center"><i class="fa-regular fa-clock" style="color:#60a5fa"></i></div>
                              <span style="font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:100px;background:rgba(6,182,212,.1);color:#60a5fa">? 0.3s faster</span>
                           </div>
                           <div class="db-stat-val" style="color:#60a5fa">1.4s</div>
                           <div class="db-stat-lbl">Avg Response Time</div>
                        </div>
                     </div>
                     <div class="col-6 col-xl-3">
                        <div class="db-stat-card">
                           <div class="d-flex justify-content-between align-items-start mb-3">
                              <div style="width:38px;height:38px;border-radius:11px;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-coins" style="color:#fbbf24"></i></div>
                              <span style="font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:100px;background:rgba(245,158,11,.1);color:#fbbf24">? 31%</span>
                           </div>
                           <div class="db-stat-val" style="color:#fbbf24">$18.2K</div>
                           <div class="db-stat-lbl">Monthly Cost Savings</div>
                        </div>
                     </div>
                  </div>
                  <!-- Chart + Activity -->
                  <div class="row g-3">
                     <div class="col-lg-8">
                        <div style="background:var(--sf);border:1px solid var(--bd);border-radius:18px;padding:24px">
                           <div class="d-flex align-items-center justify-content-between mb-3">
                              <div style="font-weight:600"><i class="fa-solid fa-chart-area me-2" style="color:var(--pur)"></i>Conversation Volume</div>
                              <div style="font-size:.75rem;color:var(--tx3)">Last 30 days</div>
                           </div>
                           <canvas id="ovChart" height="100"></canvas>
                        </div>
                     </div>
                     <div class="col-lg-4">
                        <div style="background:var(--sf);border:1px solid var(--bd);border-radius:18px;padding:20px;height:100%">
                           <div class="d-flex align-items-center justify-content-between mb-3">
                              <div style="font-weight:600;font-size:.9rem">Live Activity</div>
                              <div class="d-flex align-items-center gap-2"><span style="width:7px;height:7px;border-radius:50%;background:#34d399;animation:bpls 1.5s infinite"></span><span style="font-size:.72rem;color:#34d399;font-weight:600">Live</span></div>
                           </div>
                           <div id="liveActivity" style="display:flex;flex-direction:column;gap:8px">
                              <div style="display:flex;gap:10px;padding:10px;background:var(--bg3);border-radius:10px;font-size:.78rem"><span style="width:7px;height:7px;border-radius:50%;background:#34d399;margin-top:4px;flex-shrink:0"></span><span style="color:var(--tx2)">AI resolved billing query for user@acme.com</span><span style="margin-left:auto;color:var(--tx3);white-space:nowrap">2s ago</span></div>
                              <div style="display:flex;gap:10px;padding:10px;background:var(--bg3);border-radius:10px;font-size:.78rem"><span style="width:7px;height:7px;border-radius:50%;background:#0ea5e9;margin-top:4px;flex-shrink:0"></span><span style="color:var(--tx2)">Sales agent qualified lead from LinkedIn</span><span style="margin-left:auto;color:var(--tx3);white-space:nowrap">18s ago</span></div>
                              <div style="display:flex;gap:10px;padding:10px;background:var(--bg3);border-radius:10px;font-size:.78rem"><span style="width:7px;height:7px;border-radius:50%;background:#34d399;margin-top:4px;flex-shrink:0"></span><span style="color:var(--tx2)">CRM synced � 12 contacts updated</span><span style="margin-left:auto;color:var(--tx3);white-space:nowrap">45s ago</span></div>
                              <div style="display:flex;gap:10px;padding:10px;background:var(--bg3);border-radius:10px;font-size:.78rem"><span style="width:7px;height:7px;border-radius:50%;background:#fbbf24;margin-top:4px;flex-shrink:0"></span><span style="color:var(--tx2)">Escalation: complex refund case ? human</span><span style="margin-left:auto;color:var(--tx3);white-space:nowrap">1m ago</span></div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- -- AI AGENTS -- -->
               <div class="db-section" id="sec-agents">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                     <div>
                        <h4 style="font-size:1.4rem;font-weight:700;margin-bottom:4px">AI Agents</h4>
                        <p style="font-size:.875rem;color:var(--tx3);margin:0">Manage and monitor your deployed AI agents.</p>
                     </div>
                     <button class="bgrd btn px-3 py-2" style="font-size:.85rem"><i class="fa-solid fa-plus me-2"></i>Deploy New Agent</button>
                  </div>
                  <div class="row g-3">
                     <div class="col-md-6">
                        <div class="agent-card">
                           <div class="d-flex align-items-start gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa-solid fa-headset" style="color:#34d399;font-size:1.1rem"></i></div>
                              <div style="flex:1">
                                 <div class="d-flex align-items-center gap-2 mb-1"><strong>Support Bot v2.1</strong><span style="width:9px;height:9px;border-radius:50%;background:#34d399;box-shadow:0 0 8px #34d399"></span></div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Customer Support � GPT-4o</div>
                              </div>
                              <span class="bst son">Online</span>
                           </div>
                           <div class="row g-2 mb-3">
                              <div class="col-6">
                                 <div style="background:var(--bg3);border-radius:10px;padding:10px">
                                    <div style="font-size:1.1rem;font-weight:700">1,248</div>
                                    <div style="font-size:.7rem;color:var(--tx3)">Chats today</div>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div style="background:var(--bg3);border-radius:10px;padding:10px">
                                    <div style="font-size:1.1rem;font-weight:700;color:#34d399">96.4%</div>
                                    <div style="font-size:.7rem;color:var(--tx3)">Success rate</div>
                                 </div>
                              </div>
                           </div>
                           <div class="d-flex gap-2"><button class="boc btn flex-fill py-2" style="font-size:.82rem"><i class="fa-solid fa-eye me-1"></i>View</button><button class="bgrd btn flex-fill py-2" style="font-size:.82rem"><i class="fa-solid fa-sliders me-1"></i>Configure</button></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="agent-card">
                           <div class="d-flex align-items-start gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(14,165,233,.12);border:1px solid rgba(14,165,233,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa-solid fa-handshake" style="color:#38bdf8;font-size:1.1rem"></i></div>
                              <div style="flex:1">
                                 <div class="d-flex align-items-center gap-2 mb-1"><strong>Sales Qualifier</strong><span style="width:9px;height:9px;border-radius:50%;background:#0ea5e9;box-shadow:0 0 8px #0ea5e9"></span></div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Sales Automation � Claude 3.5</div>
                              </div>
                              <span class="bst sbz">Busy</span>
                           </div>
                           <div class="row g-2 mb-3">
                              <div class="col-6">
                                 <div style="background:var(--bg3);border-radius:10px;padding:10px">
                                    <div style="font-size:1.1rem;font-weight:700">347</div>
                                    <div style="font-size:.7rem;color:var(--tx3)">Leads qualified</div>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div style="background:var(--bg3);border-radius:10px;padding:10px">
                                    <div style="font-size:1.1rem;font-weight:700;color:#38bdf8">78.2%</div>
                                    <div style="font-size:.7rem;color:var(--tx3)">Conv. rate</div>
                                 </div>
                              </div>
                           </div>
                           <div class="d-flex gap-2"><button class="boc btn flex-fill py-2" style="font-size:.82rem"><i class="fa-solid fa-eye me-1"></i>View</button><button class="bgrd btn flex-fill py-2" style="font-size:.82rem"><i class="fa-solid fa-sliders me-1"></i>Configure</button></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="agent-card">
                           <div class="d-flex align-items-start gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(6,182,212,.1);border:1px solid rgba(6,182,212,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa-solid fa-chart-bar" style="color:#60a5fa;font-size:1.1rem"></i></div>
                              <div style="flex:1">
                                 <div class="d-flex align-items-center gap-2 mb-1"><strong>Data Analyzer</strong><span style="width:9px;height:9px;border-radius:50%;background:#34d399;box-shadow:0 0 8px #34d399"></span></div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Analytics � Gemini Pro</div>
                              </div>
                              <span class="bst son">Online</span>
                           </div>
                           <div class="row g-2 mb-3">
                              <div class="col-6">
                                 <div style="background:var(--bg3);border-radius:10px;padding:10px">
                                    <div style="font-size:1.1rem;font-weight:700">92</div>
                                    <div style="font-size:.7rem;color:var(--tx3)">Reports generated</div>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div style="background:var(--bg3);border-radius:10px;padding:10px">
                                    <div style="font-size:1.1rem;font-weight:700;color:#60a5fa">100%</div>
                                    <div style="font-size:.7rem;color:var(--tx3)">Accuracy</div>
                                 </div>
                              </div>
                           </div>
                           <div class="d-flex gap-2"><button class="boc btn flex-fill py-2" style="font-size:.82rem"><i class="fa-solid fa-eye me-1"></i>View</button><button class="bgrd btn flex-fill py-2" style="font-size:.82rem"><i class="fa-solid fa-sliders me-1"></i>Configure</button></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="agent-card">
                           <div class="d-flex align-items-start gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa-solid fa-envelope" style="color:#fbbf24;font-size:1.1rem"></i></div>
                              <div style="flex:1">
                                 <div class="d-flex align-items-center gap-2 mb-1"><strong>Email Automator</strong><span style="width:9px;height:9px;border-radius:50%;background:#f59e0b"></span></div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Email Marketing � GPT-4o</div>
                              </div>
                              <span class="bst sid">Idle</span>
                           </div>
                           <div class="row g-2 mb-3">
                              <div class="col-6">
                                 <div style="background:var(--bg3);border-radius:10px;padding:10px">
                                    <div style="font-size:1.1rem;font-weight:700">538</div>
                                    <div style="font-size:.7rem;color:var(--tx3)">Emails sent</div>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div style="background:var(--bg3);border-radius:10px;padding:10px">
                                    <div style="font-size:1.1rem;font-weight:700;color:#fbbf24">41.3%</div>
                                    <div style="font-size:.7rem;color:var(--tx3)">Open rate</div>
                                 </div>
                              </div>
                           </div>
                           <div class="d-flex gap-2"><button class="boc btn flex-fill py-2" style="font-size:.82rem"><i class="fa-solid fa-eye me-1"></i>View</button><button class="bgrd btn flex-fill py-2" style="font-size:.82rem"><i class="fa-solid fa-sliders me-1"></i>Configure</button></div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- -- AI CHAT (Anthropic API powered) -- -->
               <div class="db-section" id="sec-chat">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                     <div>
                        <h4 style="font-size:1.4rem;font-weight:700;margin-bottom:4px">AI Chat Console</h4>
                        <p style="font-size:.875rem;color:var(--tx3);margin:0">Chat with your InvoSync Jo assistant � powered by Claude.</p>
                     </div>
                     <button class="boc btn px-3 py-2" style="font-size:.82rem" onclick="clearChat()"><i class="fa-solid fa-rotate-right me-1"></i>New Chat</button>
                  </div>
                  <div class="row g-3">
                     <div class="col-lg-8">
                        <div class="chat-wrap">
                           <div class="chat-header">
                              <div class="chat-ai-avatar"><i class="fa-solid fa-robot"></i></div>
                              <div>
                                 <div style="font-weight:600;font-size:.9rem">InvoSync Jo Assistant</div>
                                 <div class="chat-status"><span style="width:7px;height:7px;border-radius:50%;background:#34d399;animation:bpls 2s infinite"></span>Online � Powered by Claude</div>
                              </div>
                           </div>
                           <div class="chat-body" id="chatBody">
                              <div class="d-flex flex-column gap-1">
                                 <div class="msg msg-ai">?? Hi! I'm your InvoSync Jo assistant. I can help you with support analytics, agent configuration, automation workflows, and business insights. What would you like to know?</div>
                                 <div class="msg-time" style="align-self:flex-start;padding-left:4px">InvoSync Jo � Just now</div>
                              </div>
                           </div>
                           <div class="chat-input-wrap">
                              <textarea class="chat-inp" id="chatInp" placeholder="Ask anything � analytics, automations, agent performance..." rows="1" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendChat()}"></textarea>
                              <button class="chat-send" id="chatSendBtn" onclick="sendChat()"><i class="fa-solid fa-paper-plane fa-sm"></i></button>
                           </div>
                        </div>
                     </div>
                     <div class="col-lg-4">
                        <div style="background:var(--sf);border:1px solid var(--bd);border-radius:18px;padding:20px;margin-bottom:14px">
                           <div style="font-weight:600;font-size:.85rem;margin-bottom:14px;color:var(--tx3);text-transform:uppercase;letter-spacing:.06em"><i class="fa-solid fa-bolt me-1" style="color:var(--pur)"></i>Quick Actions</div>
                           <div style="display:flex;flex-direction:column;gap:8px">
                              <button class="boc btn py-2 text-start px-3" style="font-size:.82rem;border-radius:10px" onclick="quickMsg('Give me a summary of today\'s support metrics')"><i class="fa-solid fa-chart-bar me-2" style="color:var(--pur)"></i>Today's metrics</button>
                              <button class="boc btn py-2 text-start px-3" style="font-size:.82rem;border-radius:10px" onclick="quickMsg('Which AI agent is performing best this week?')"><i class="fa-solid fa-robot me-2" style="color:#34d399"></i>Best performing agent</button>
                              <button class="boc btn py-2 text-start px-3" style="font-size:.82rem;border-radius:10px" onclick="quickMsg('What automation should I set up to save the most time?')"><i class="fa-solid fa-bolt me-2" style="color:#fbbf24"></i>Automation suggestions</button>
                              <button class="boc btn py-2 text-start px-3" style="font-size:.82rem;border-radius:10px" onclick="quickMsg('How can I reduce my average response time?')"><i class="fa-regular fa-clock me-2" style="color:#60a5fa"></i>Improve response time</button>
                              <button class="boc btn py-2 text-start px-3" style="font-size:.82rem;border-radius:10px" onclick="quickMsg('Generate a weekly performance report')"><i class="fa-solid fa-file-lines me-2" style="color:#38bdf8"></i>Generate report</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- -- ANALYTICS -- -->
               <div class="db-section" id="sec-analytics">
                  <div class="mb-4">
                     <h4 style="font-size:1.4rem;font-weight:700;margin-bottom:4px">Analytics</h4>
                     <p style="font-size:.875rem;color:var(--tx3);margin:0">Deep insights into your AI performance and business impact.</p>
                  </div>
                  <div class="row g-3 mb-4">
                     <div class="col-md-3">
                        <div class="db-stat-card">
                           <div class="d-flex justify-content-between mb-3">
                              <div style="width:36px;height:36px;border-radius:10px;background:rgba(14,165,233,.12);display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-chart-line" style="color:#38bdf8;font-size:.85rem"></i></div>
                              <span style="font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:100px;background:rgba(52,211,153,.1);color:#34d399">? 24.3%</span>
                           </div>
                           <div class="db-stat-val gt">$128K</div>
                           <div class="db-stat-lbl">Revenue from AI</div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="db-stat-card">
                           <div class="d-flex justify-content-between mb-3">
                              <div style="width:36px;height:36px;border-radius:10px;background:rgba(52,211,153,.1);display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-star" style="color:#34d399;font-size:.85rem"></i></div>
                              <span style="font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:100px;background:rgba(52,211,153,.1);color:#34d399">? 0.3</span>
                           </div>
                           <div class="db-stat-val" style="color:#34d399">4.9?</div>
                           <div class="db-stat-lbl">Avg CSAT Score</div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="db-stat-card">
                           <div class="d-flex justify-content-between mb-3">
                              <div style="width:36px;height:36px;border-radius:10px;background:rgba(6,182,212,.1);display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-users" style="color:#60a5fa;font-size:.85rem"></i></div>
                              <span style="font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:100px;background:rgba(52,211,153,.1);color:#34d399">? 12%</span>
                           </div>
                           <div class="db-stat-val" style="color:#60a5fa">8,420</div>
                           <div class="db-stat-lbl">Unique users served</div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="db-stat-card">
                           <div class="d-flex justify-content-between mb-3">
                              <div style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-percent" style="color:#fbbf24;font-size:.85rem"></i></div>
                              <span style="font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:100px;background:rgba(52,211,153,.1);color:#34d399">? 5%</span>
                           </div>
                           <div class="db-stat-val" style="color:#fbbf24">81%</div>
                           <div class="db-stat-lbl">AI containment rate</div>
                        </div>
                     </div>
                  </div>
                  <div class="row g-3">
                     <div class="col-lg-8">
                        <div style="background:var(--sf);border:1px solid var(--bd);border-radius:18px;padding:24px">
                           <div class="d-flex align-items-center justify-content-between mb-3">
                              <div style="font-weight:600"><i class="fa-solid fa-chart-area me-2" style="color:var(--pur)"></i>Monthly Performance Trend</div>
                              <div style="font-size:.75rem;color:var(--tx3)">Last 12 months</div>
                           </div>
                           <canvas id="anChart" height="100"></canvas>
                        </div>
                     </div>
                     <div class="col-lg-4">
                        <div style="background:var(--sf);border:1px solid var(--bd);border-radius:18px;padding:20px">
                           <div style="font-weight:600;font-size:.9rem;margin-bottom:16px">Agent Performance</div>
                           <div class="d-flex flex-column gap-3">
                              <div>
                                 <div class="d-flex justify-content-between mb-1" style="font-size:.82rem"><span>Support Bot</span><span style="color:#34d399;font-weight:600">96.4%</span></div>
                                 <div class="rtbar">
                                    <div class="rtfill" style="width:96%"></div>
                                 </div>
                              </div>
                              <div>
                                 <div class="d-flex justify-content-between mb-1" style="font-size:.82rem"><span>Sales Qualifier</span><span style="color:#38bdf8;font-weight:600">78.2%</span></div>
                                 <div class="rtbar">
                                    <div class="rtfill" style="width:78%"></div>
                                 </div>
                              </div>
                              <div>
                                 <div class="d-flex justify-content-between mb-1" style="font-size:.82rem"><span>Data Analyzer</span><span style="color:#60a5fa;font-weight:600">100%</span></div>
                                 <div class="rtbar">
                                    <div class="rtfill" style="width:100%"></div>
                                 </div>
                              </div>
                              <div>
                                 <div class="d-flex justify-content-between mb-1" style="font-size:.82rem"><span>Email Automator</span><span style="color:#fbbf24;font-weight:600">41.3%</span></div>
                                 <div class="rtbar">
                                    <div class="rtfill" style="width:41%"></div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- -- AUTOMATIONS -- -->
               <div class="db-section" id="sec-automations">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                     <div>
                        <h4 style="font-size:1.4rem;font-weight:700;margin-bottom:4px">Automations</h4>
                        <p style="font-size:.875rem;color:var(--tx3);margin:0">Manage your active workflow automations.</p>
                     </div>
                     <button class="bgrd btn px-3 py-2" style="font-size:.85rem"><i class="fa-solid fa-plus me-2"></i>Create Automation</button>
                  </div>
                  <div style="background:var(--sf);border:1px solid var(--bd);border-radius:18px;overflow:hidden">
                     <table class="db-table">
                        <thead>
                           <tr>
                              <th>Automation</th>
                              <th>Trigger</th>
                              <th>Runs Today</th>
                              <th>Status</th>
                              <th>Actions</th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr>
                              <td>
                                 <div style="font-weight:600">Auto-resolve billing tickets</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">Support Bot ? Billing module</div>
                              </td>
                              <td style="font-size:.82rem">New ticket + billing tag</td>
                              <td style="font-weight:600;color:#34d399">248</td>
                              <td><span class="bst son">Active</span></td>
                              <td><button class="boc btn px-2 py-1" style="font-size:.75rem;border-radius:8px"><i class="fa-solid fa-pencil"></i></button></td>
                           </tr>
                           <tr>
                              <td>
                                 <div style="font-weight:600">Welcome email sequence</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">Email Automator ? Onboarding</div>
                              </td>
                              <td style="font-size:.82rem">New user signup</td>
                              <td style="font-weight:600;color:#38bdf8">93</td>
                              <td><span class="bst sbz">Running</span></td>
                              <td><button class="boc btn px-2 py-1" style="font-size:.75rem;border-radius:8px"><i class="fa-solid fa-pencil"></i></button></td>
                           </tr>
                           <tr>
                              <td>
                                 <div style="font-weight:600">CRM data sync</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">Data Analyzer ? HubSpot</div>
                              </td>
                              <td style="font-size:.82rem">Every 30 minutes</td>
                              <td style="font-weight:600;color:#60a5fa">48</td>
                              <td><span class="bst son">Active</span></td>
                              <td><button class="boc btn px-2 py-1" style="font-size:.75rem;border-radius:8px"><i class="fa-solid fa-pencil"></i></button></td>
                           </tr>
                           <tr>
                              <td>
                                 <div style="font-weight:600">Escalation alerts</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">Support Bot ? Slack #escalations</div>
                              </td>
                              <td style="font-size:.82rem">Low confidence score &lt; 70%</td>
                              <td style="font-weight:600;color:#fbbf24">9</td>
                              <td><span class="bst sid">Standby</span></td>
                              <td><button class="boc btn px-2 py-1" style="font-size:.75rem;border-radius:8px"><i class="fa-solid fa-pencil"></i></button></td>
                           </tr>
                           <tr>
                              <td>
                                 <div style="font-weight:600">Lead scoring pipeline</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">Sales Qualifier ? Salesforce</div>
                              </td>
                              <td style="font-size:.82rem">New lead form submission</td>
                              <td style="font-weight:600;color:#34d399">127</td>
                              <td><span class="bst son">Active</span></td>
                              <td><button class="boc btn px-2 py-1" style="font-size:.75rem;border-radius:8px"><i class="fa-solid fa-pencil"></i></button></td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>
               <!-- -- INTEGRATIONS -- -->
               <div class="db-section" id="sec-integrations">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                     <div>
                        <h4 style="font-size:1.4rem;font-weight:700;margin-bottom:4px">Integrations</h4>
                        <p style="font-size:.875rem;color:var(--tx3);margin:0">Manage your connected apps and services.</p>
                     </div>
                     <button class="bgrd btn px-3 py-2" style="font-size:.85rem"><i class="fa-solid fa-plus me-2"></i>Add Integration</button>
                  </div>
                  <div class="row g-3">
                     <div class="col-md-4">
                        <div class="gc p-4">
                           <div class="d-flex align-items-center gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(74,21,75,.15);display:flex;align-items:center;justify-content:center"><i class="fa-brands fa-slack fa-xl" style="color:#38bdf8"></i></div>
                              <div>
                                 <div class="fw-semibold">Slack</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">#support, #escalations</div>
                              </div>
                              <span class="bst son ms-auto">Connected</span>
                           </div>
                           <div style="font-size:.8rem;color:var(--tx2);margin-bottom:12px">Bi-directional sync enabled. 3 channels active.</div>
                           <button class="boc btn w-100 py-2" style="font-size:.82rem"><i class="fa-solid fa-gear me-1"></i>Configure</button>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="gc p-4">
                           <div class="d-flex align-items-center gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(36,41,47,.15);display:flex;align-items:center;justify-content:center"><i class="fa-brands fa-github fa-xl" style="color:var(--tx2)"></i></div>
                              <div>
                                 <div class="fw-semibold">GitHub</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">nexus-ai/main</div>
                              </div>
                              <span class="bst son ms-auto">Connected</span>
                           </div>
                           <div style="font-size:.8rem;color:var(--tx2);margin-bottom:12px">AI code review active. 2 repos monitored.</div>
                           <button class="boc btn w-100 py-2" style="font-size:.82rem"><i class="fa-solid fa-gear me-1"></i>Configure</button>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="gc p-4">
                           <div class="d-flex align-items-center gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(52,168,83,.1);display:flex;align-items:center;justify-content:center"><i class="fa-brands fa-google-drive fa-xl" style="color:#34d399"></i></div>
                              <div>
                                 <div class="fw-semibold">Google Drive</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">My Drive + Team Drive</div>
                              </div>
                              <span class="bst son ms-auto">Connected</span>
                           </div>
                           <div style="font-size:.8rem;color:var(--tx2);margin-bottom:12px">234 documents indexed for AI training.</div>
                           <button class="boc btn w-100 py-2" style="font-size:.82rem"><i class="fa-solid fa-gear me-1"></i>Configure</button>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="gc p-4">
                           <div class="d-flex align-items-center gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(255,102,0,.1);display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-bolt fa-xl" style="color:#ff6600"></i></div>
                              <div>
                                 <div class="fw-semibold">Zapier</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">12 Zaps active</div>
                              </div>
                              <span class="bst son ms-auto">Connected</span>
                           </div>
                           <div style="font-size:.8rem;color:var(--tx2);margin-bottom:12px">Connecting to HubSpot, Stripe, Mailchimp.</div>
                           <button class="boc btn w-100 py-2" style="font-size:.82rem"><i class="fa-solid fa-gear me-1"></i>Configure</button>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="gc p-4">
                           <div class="d-flex align-items-center gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(88,101,242,.15);display:flex;align-items:center;justify-content:center"><i class="fa-brands fa-discord fa-xl" style="color:#7289da"></i></div>
                              <div>
                                 <div class="fw-semibold">Discord</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">1 server, 4 channels</div>
                              </div>
                              <span class="bst son ms-auto">Connected</span>
                           </div>
                           <div style="font-size:.8rem;color:var(--tx2);margin-bottom:12px">Community bot answering 94% of questions.</div>
                           <button class="boc btn w-100 py-2" style="font-size:.82rem"><i class="fa-solid fa-gear me-1"></i>Configure</button>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="gc p-4">
                           <div class="d-flex align-items-center gap-3 mb-3">
                              <div style="width:46px;height:46px;border-radius:14px;background:rgba(0,0,0,.15);display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-book-open fa-xl" style="color:var(--tx2)"></i></div>
                              <div>
                                 <div class="fw-semibold">Notion</div>
                                 <div style="font-size:.75rem;color:var(--tx3)">Team workspace</div>
                              </div>
                              <span class="bst sbz ms-auto">Syncing</span>
                           </div>
                           <div style="font-size:.8rem;color:var(--tx2);margin-bottom:12px">178 pages indexed. Syncing in progress.</div>
                           <button class="boc btn w-100 py-2" style="font-size:.82rem"><i class="fa-solid fa-gear me-1"></i>Configure</button>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- -- SETTINGS -- -->
               <div class="db-section" id="sec-settings">
                  <div class="mb-4">
                     <h4 style="font-size:1.4rem;font-weight:700;margin-bottom:4px">Settings</h4>
                     <p style="font-size:.875rem;color:var(--tx3);margin:0">Manage your account and preferences.</p>
                  </div>
                  <div class="row g-3">
                     <div class="col-lg-6">
                        <div style="background:var(--sf);border:1px solid var(--bd);border-radius:18px;padding:24px;margin-bottom:16px">
                           <div style="font-weight:600;font-size:1rem;margin-bottom:20px"><i class="fa-regular fa-user me-2" style="color:var(--pur)"></i>Profile</div>
                           <div class="d-flex align-items-center gap-3 mb-4">
                              <div class="db-avatar" id="settingsAvatar" style="width:56px;height:56px;font-size:1.2rem;border-radius:16px">U</div>
                              <div>
                                 <div id="settingsName" style="font-weight:600;font-size:1rem">User Name</div>
                                 <div id="settingsEmail" style="font-size:.82rem;color:var(--tx3)">user@email.com</div>
                                 <div style="margin-top:4px"><span class="bst son" style="font-size:.68rem">Pro Plan</span></div>
                              </div>
                           </div>
                           <div style="margin-bottom:12px"><label class="olbl">Display Name</label><input class="oinp" type="text" id="profileName" placeholder="Your name"></div>
                           <div style="margin-bottom:12px"><label class="olbl">Email</label><input class="oinp" type="email" id="profileEmail" placeholder="your@email.com"></div>
                           <button class="bgrd btn px-4 py-2" style="font-size:.875rem">Save Changes</button>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div style="background:var(--sf);border:1px solid var(--bd);border-radius:18px;padding:24px">
                           <div style="font-weight:600;font-size:1rem;margin-bottom:20px"><i class="fa-solid fa-toggle-on me-2" style="color:var(--pur)"></i>Preferences</div>
                           <div class="setting-row">
                              <div>
                                 <div style="font-size:.875rem;font-weight:500">Dark Mode</div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Toggle interface theme</div>
                              </div>
                              <label class="toggle-switch"><input type="checkbox" id="darkModeToggle" checked onchange="toggleTheme()"><span class="toggle-thumb"></span></label>
                           </div>
                           <div class="setting-row">
                              <div>
                                 <div style="font-size:.875rem;font-weight:500">Email Notifications</div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Daily performance digest</div>
                              </div>
                              <label class="toggle-switch"><input type="checkbox" checked><span class="toggle-thumb"></span></label>
                           </div>
                           <div class="setting-row">
                              <div>
                                 <div style="font-size:.875rem;font-weight:500">AI Auto-escalation</div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Escalate when confidence &lt; 70%</div>
                              </div>
                              <label class="toggle-switch"><input type="checkbox" checked><span class="toggle-thumb"></span></label>
                           </div>
                           <div class="setting-row">
                              <div>
                                 <div style="font-size:.875rem;font-weight:500">Usage Analytics</div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Share anonymous usage data</div>
                              </div>
                              <label class="toggle-switch"><input type="checkbox"><span class="toggle-thumb"></span></label>
                           </div>
                           <div class="setting-row" style="border:none">
                              <div>
                                 <div style="font-size:.875rem;font-weight:500">Slack Alerts</div>
                                 <div style="font-size:.78rem;color:var(--tx3)">Real-time critical notifications</div>
                              </div>
                              <label class="toggle-switch"><input type="checkbox" checked><span class="toggle-thumb"></span></label>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- /db-content -->
         </div>
         <!-- /db-main -->
      </div>
      <!-- /dashboard -->
      <!-- ======================== SCRIPTS ======================== -->
      <!-- jQuery -->
      <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
      <!-- Bootstrap 5 -->
      <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
      <!-- AOS -->
      <script src="{{ asset('assets/js/aos.js') }}"></script>
      <!-- Swiper -->
      <script src="{{ asset('assets/js/chart.umd.min.js') }}"></script>
      <!-- CounterUp -->
      <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
      <!-- Main js -->
      <script src="{{ asset('assets/js/main.js') }}"></script>
   </body>
</html>
