@extends('layouts.guest')

@section('content')
       <!-- HERO -->
         <section id="hero">
            <div class="aur aur-a" style="top:-80px;left:-120px"></div>
            <div class="aur aur-b" style="top:180px;right:-180px"></div>
            <div class="aur aur-a" style="bottom:-80px;left:45%;transform:translateX(-50%);opacity:.4"></div>
            <div class="container position-relative" style="z-index:2">
               <div class="text-center">
                  <div class="afu" style="animation-delay:.05s"><span class="hbadge"><span class="bdot"></span>Now with GPT-4o &amp; Claude 3.5 integration</span></div>
                  <h1 class="h1 afu" style="animation-delay:.12s">Automate Your Business<br>with <span class="gt">AI Agents</span></h1>
                  <p class="mx-auto afu" style="max-width:580px;font-size:clamp(.95rem,1.8vw,1.2rem);color:var(--tx2);margin-bottom:36px;animation-delay:.2s">Deploy intelligent AI agents that handle customer support, automate workflows, analyze data, and scale your operations � without writing a single line of code.</p>
                  <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap afu" style="animation-delay:.28s">
                     <button class="bgrd btn px-4 py-3 fs-6" data-bs-toggle="offcanvas" data-bs-target="#lofc" onclick="swTab('signup')">Start for Free � No Credit Card</button>
                     <a href="https://www.youtube.com/watch?v=ScMzIvxBSi4" class="boc btn px-4 py-3 fs-6 vidpop"><i class="fa-solid fa-circle-play me-2" style="color:var(--pur)"></i>Watch Demo</a>
                  </div>
                  <div class="mt-5 afu" style="animation-delay:.4s">
                     <p style="font-size:.71rem;color:var(--tx3);text-transform:uppercase;letter-spacing:.12em;margin-bottom:14px">Trusted by innovative teams at</p>
                     <div class="d-flex align-items-center justify-content-center gap-4 flex-wrap">
                        <span class="tlog">Stripe</span><span class="tlog">Notion</span><span class="tlog">Vercel</span><span class="tlog">Linear</span><span class="tlog">Figma</span><span class="tlog">Loom</span>
                     </div>
                  </div>
               </div>
               <div class="row justify-content-center mt-5">
                  <div class="col-lg-11 adi">
                     <div class="dwrap">
                        <div class="dtbar"><span class="dd" style="background:#ff5f57"></span><span class="dd" style="background:#ffbd2e"></span><span class="dd" style="background:#28c840"></span><span class="ms-auto me-auto" style="font-size:.76rem;color:var(--tx3)">InvoSync Jo Dashboard � nexus.ai/dashboard</span></div>
                        <div class="dgrid">
                           <div class="dside">
                              <div style="font-size:.64rem;text-transform:uppercase;letter-spacing:.1em;color:var(--tx3);padding:0 10px 10px;font-weight:700">Overview</div>
                              <button class="dsi on"><i class="fa-solid fa-gauge-high"></i> Dashboard</button>
                              <button class="dsi"><i class="fa-solid fa-robot"></i> Agents</button>
                              <button class="dsi"><i class="fa-solid fa-chart-line"></i> Analytics</button>
                              <button class="dsi"><i class="fa-regular fa-comments"></i> Conversations</button>
                              <button class="dsi"><i class="fa-solid fa-bolt"></i> Automations</button>
                           </div>
                           <div class="p-3">
                              <div class="row g-2 mb-3">
                                 <div class="col-6 col-sm-3">
                                    <div class="stpill">
                                       <div style="font-size:1.4rem;font-weight:700" class="gt">24.8K</div>
                                       <div style="font-size:.67rem;color:var(--tx3)">AI Conversations</div>
                                       <div style="font-size:.67rem;color:#34d399;font-weight:600"><i class="fa-solid fa-caret-up me-1"></i>18.4%</div>
                                    </div>
                                 </div>
                                 <div class="col-6 col-sm-3">
                                    <div class="stpill">
                                       <div style="font-size:1.4rem;font-weight:700">98.2%</div>
                                       <div style="font-size:.67rem;color:var(--tx3)">Resolution Rate</div>
                                       <div style="font-size:.67rem;color:#34d399;font-weight:600"><i class="fa-solid fa-caret-up me-1"></i>3.1%</div>
                                    </div>
                                 </div>
                                 <div class="col-6 col-sm-3">
                                    <div class="stpill">
                                       <div style="font-size:1.4rem;font-weight:700">1.4s</div>
                                       <div style="font-size:.67rem;color:var(--tx3)">Avg Response</div>
                                       <div style="font-size:.67rem;color:#60a5fa;font-weight:600"><i class="fa-solid fa-caret-down me-1"></i>0.3s</div>
                                    </div>
                                 </div>
                                 <div class="col-6 col-sm-3">
                                    <div class="stpill">
                                       <div style="font-size:1.4rem;font-weight:700">$18.2K</div>
                                       <div style="font-size:.67rem;color:var(--tx3)">Cost Saved</div>
                                       <div style="font-size:.67rem;color:#34d399;font-weight:600"><i class="fa-solid fa-caret-up me-1"></i>31%</div>
                                    </div>
                                 </div>
                              </div>
                              <div class="row g-2">
                                 <div class="col-sm-7">
                                    <div style="background:var(--bg3);border:1px solid var(--bd);border-radius:12px;padding:14px">
                                       <div style="font-size:.73rem;color:var(--tx3);margin-bottom:10px;font-weight:600"><i class="fa-solid fa-chart-bar me-1"></i>Agent Activity � Last 7 days</div>
                                       <div style="display:flex;align-items:flex-end;gap:5px;height:76px">
                                          <div class="bbar" style="height:45%"></div>
                                          <div class="bbar" style="height:72%"></div>
                                          <div class="bbar" style="height:55%"></div>
                                          <div class="bbar" style="height:88%"></div>
                                          <div class="bbar" style="height:63%"></div>
                                          <div class="bbar" style="height:95%"></div>
                                          <div class="bbar" style="height:78%"></div>
                                       </div>
                                       <div class="d-flex justify-content-between mt-2" style="font-size:.62rem;color:var(--tx3)"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
                                    </div>
                                 </div>
                                 <div class="col-sm-5">
                                    <div style="background:var(--bg3);border:1px solid var(--bd);border-radius:12px;padding:12px;height:100%;display:flex;flex-direction:column;gap:8px">
                                       <div style="font-size:.71rem;color:var(--tx3);font-weight:600"><span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--pur);box-shadow:0 0 6px var(--pur);margin-right:6px;animation:bpls 2s infinite"></span>Live Agent</div>
                                       <div class="cbbl cbus">Summarize today's tickets</div>
                                       <div class="cbbl cbai"><strong>47 tickets.</strong> 38 AI-resolved (81%). Top: billing (23%).</div>
                                       <div style="display:flex;gap:4px;padding:8px 12px">
                                          <div class="tdot"></div>
                                          <div class="tdot" style="animation-delay:.15s"></div>
                                          <div class="tdot" style="animation-delay:.3s"></div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- SOCIAL PROOF -->
         <section id="proof">
            <div class="container">
               <div class="row g-3 align-items-center text-center">
                  <div class="col-6 col-sm-3">
                     <div class="pnum">4.9/5</div>
                     <div class="plbl"><i class="fa-solid fa-star text-warning me-1"></i>Avg Rating</div>
                  </div>
                  <div class="col-6 col-sm-3">
                     <div class="pnum">50K+</div>
                     <div class="plbl">Active Users</div>
                  </div>
                  <div class="col-6 col-sm-3">
                     <div class="pnum">200M+</div>
                     <div class="plbl">Automations Run</div>
                  </div>
                  <div class="col-6 col-sm-3">
                     <div class="pnum">99.9%</div>
                     <div class="plbl">Uptime SLA</div>
                  </div>
               </div>
               <div class="lscroll">
                  <div class="ltrack"><span class="lbr">Stripe</span><span class="lbr">Notion</span><span class="lbr">Vercel</span><span class="lbr">Linear</span><span class="lbr">Figma</span><span class="lbr">Shopify</span><span class="lbr">Intercom</span><span class="lbr">HubSpot</span><span class="lbr">Loom</span><span class="lbr">Stripe</span><span class="lbr">Notion</span><span class="lbr">Vercel</span><span class="lbr">Linear</span><span class="lbr">Figma</span><span class="lbr">Shopify</span><span class="lbr">Intercom</span><span class="lbr">HubSpot</span><span class="lbr">Loom</span></div>
               </div>
            </div>
         </section>
         <!-- PROBLEM -->
         <section class="sp position-relative">
            <div class="aur aur-b" style="top:50%;right:-200px;transform:translateY(-50%)"></div>
            <div class="container position-relative" style="z-index:1">
               <div class="text-center mb-5 rv">
                  <span class="slbl">The Problem</span>
                  <h2 class="stitle">Your workflow is <span class="gt">broken</span></h2>
                  <p class="ssub mx-auto">Teams waste thousands of hours on repetitive tasks that AI should handle automatically.</p>
               </div>
               <div class="row g-4">
                  <div class="col-md-4 rv">
                     <div class="gc p-4 h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;margin-bottom:18px"><i class="fa-regular fa-clock fa-lg" style="color:#f87171"></i></div>
                        <h3 class="fw-semibold fs-5 mb-2">Manual, Repetitive Work</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Your team spends 60% of their time on copy-paste tasks and repetitive queries � instead of meaningful work that drives business forward.</p>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.1s">
                     <div class="gc p-4 h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.2);display:flex;align-items:center;justify-content:center;margin-bottom:18px"><i class="fa-solid fa-dollar-sign fa-lg" style="color:#fbbf24"></i></div>
                        <h3 class="fw-semibold fs-5 mb-2">Exploding Support Costs</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Hiring more agents is expensive and doesn't scale. Customer expectations rise while response times worsen and satisfaction drops.</p>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.2s">
                     <div class="gc p-4 h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.2);display:flex;align-items:center;justify-content:center;margin-bottom:18px"><i class="fa-solid fa-plug fa-lg" style="color:#a78bfa"></i></div>
                        <h3 class="fw-semibold fs-5 mb-2">Disconnected Tools</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Your data lives in 20 different apps that don't talk to each other. Building integrations is slow, breaks constantly, and requires expensive devs.</p>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- FEATURES -->
         <section id="features" class="sp" style="background:var(--bg2)">
            <div class="container">
               <div class="text-center mb-5 rv">
                  <span class="slbl">Features</span>
                  <h2 class="stitle">Everything you need to <span class="gt">automate at scale</span></h2>
                  <p class="ssub mx-auto">Powerful AI tools for teams that want to move faster and grow without limits.</p>
               </div>
               <div class="row g-3">
                  <div class="col-md-4 rv">
                     <div class="gc p-4 h-100">
                        <div class="ftico"><i class="fa-regular fa-comment-dots"></i></div>
                        <h3 class="fs-5 fw-semibold mb-2">AI Chat &amp; Support</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Deploy a context-aware chatbot that resolves 80% of tickets instantly. Trained on your docs, policies, and past conversations.</p>
                        <span class="ftag">GPT-4o powered</span>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.05s">
                     <div class="gc p-4 h-100">
                        <div class="ftico"><i class="fa-solid fa-bolt"></i></div>
                        <h3 class="fs-5 fw-semibold mb-2">Workflow Automation</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Build complex multi-step automations with a visual builder. Connect any app, trigger on any event, run on any schedule.</p>
                        <span class="ftag">1000+ integrations</span>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.1s">
                     <div class="gc p-4 h-100">
                        <div class="ftico"><i class="fa-solid fa-chart-line"></i></div>
                        <h3 class="fs-5 fw-semibold mb-2">Deep Analytics</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Real-time dashboards showing conversation quality, automation performance, and cost savings � all in one place.</p>
                        <span class="ftag">Real-time data</span>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.15s">
                     <div class="gc p-4 h-100">
                        <div class="ftico"><i class="fa-solid fa-robot"></i></div>
                        <h3 class="fs-5 fw-semibold mb-2">AI Agents</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Autonomous agents that browse the web, write code, send emails, update CRMs, and complete complex multi-step tasks independently.</p>
                        <span class="ftag">Autonomous</span>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.2s">
                     <div class="gc p-4 h-100">
                        <div class="ftico"><i class="fa-solid fa-users"></i></div>
                        <h3 class="fs-5 fw-semibold mb-2">Team Collaboration</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Shared workspaces, role-based permissions, conversation handoffs, and live supervision for entire support teams.</p>
                        <span class="ftag">Unlimited seats</span>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.25s">
                     <div class="gc p-4 h-100">
                        <div class="ftico"><i class="fa-solid fa-code"></i></div>
                        <h3 class="fs-5 fw-semibold mb-2">Developer API</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Full REST API and webhooks for custom integrations. SDKs for JavaScript, Python, and Go. Deploy anywhere in your stack.</p>
                        <span class="ftag">API-first</span>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- HOW IT WORKS -->
         <section id="how" class="sp" style="background:var(--bg3)">
            <div class="container">
               <div class="text-center mb-5 rv">
                  <span class="slbl">How It Works</span>
                  <h2 class="stitle">Up and running in <span class="gt">3 simple steps</span></h2>
               </div>
               <div class="row g-4">
                  <div class="col-md-4 rv">
                     <div class="gc p-4 h-100 text-center">
                        <div class="hnum">1</div>
                        <h3 class="fs-5 fw-semibold mb-2">Connect Your Tools</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Link your existing apps � Slack, Notion, CRM, databases � in minutes with our one-click integration library. No code, no API keys, no headaches.</p>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.1s">
                     <div class="gc p-4 h-100 text-center">
                        <div class="hnum">2</div>
                        <h3 class="fs-5 fw-semibold mb-2">Train Your AI</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Upload your documentation, FAQs, and past conversations. InvoSync Jo learns your brand voice, products, and processes in under 10 minutes.</p>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.2s">
                     <div class="gc p-4 h-100 text-center">
                        <div class="hnum">3</div>
                        <h3 class="fs-5 fw-semibold mb-2">Automate &amp; Scale</h3>
                        <p style="font-size:.875rem;color:var(--tx2)">Your AI agents go live, handling conversations and workflows 24/7. Monitor performance in real-time and optimize continuously.</p>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- INTEGRATIONS -->
         <section id="integrations" class="sp position-relative">
            <div class="aur aur-b" style="top:50%;right:-200px;transform:translateY(-50%)"></div>
            <div class="container position-relative" style="z-index:1">
               <div class="text-center mb-5 rv">
                  <span class="slbl">Integrations</span>
                  <h2 class="stitle">Works with your <span class="gt">favorite tools</span></h2>
                  <p class="ssub mx-auto">Connect InvoSync Jo with 1,000+ apps. Everything syncs automatically so your AI always has full context.</p>
               </div>
               <div class="row g-3 justify-content-center rv">
                  <div class="col-6 col-md-4">
                     <div class="gc p-4 text-center h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(74,21,75,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="fa-brands fa-slack fa-xl" style="color:#a78bfa"></i></div>
                        <div class="fw-semibold mb-1">Slack</div>
                        <div style="font-size:.78rem;color:var(--tx3);margin-bottom:8px">Bi-directional sync &amp; AI in channels</div>
                        <span class="ftag"><i class="fa-solid fa-check me-1"></i>Connected</span>
                     </div>
                  </div>
                  <div class="col-6 col-md-4">
                     <div class="gc p-4 text-center h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(0,0,0,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="fa-solid fa-book-open fa-xl" style="color:var(--tx2)"></i></div>
                        <div class="fw-semibold mb-1">Notion</div>
                        <div style="font-size:.78rem;color:var(--tx3);margin-bottom:8px">Train AI on your knowledge base</div>
                        <span class="ftag"><i class="fa-solid fa-check me-1"></i>Connected</span>
                     </div>
                  </div>
                  <div class="col-6 col-md-4">
                     <div class="gc p-4 text-center h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(88,101,242,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="fa-brands fa-discord fa-xl" style="color:#7289da"></i></div>
                        <div class="fw-semibold mb-1">Discord</div>
                        <div style="font-size:.78rem;color:var(--tx3);margin-bottom:8px">AI bots &amp; community automation</div>
                        <span class="ftag"><i class="fa-solid fa-check me-1"></i>Connected</span>
                     </div>
                  </div>
                  <div class="col-6 col-md-4">
                     <div class="gc p-4 text-center h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(36,41,47,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="fa-brands fa-github fa-xl" style="color:var(--tx2)"></i></div>
                        <div class="fw-semibold mb-1">GitHub</div>
                        <div style="font-size:.78rem;color:var(--tx3);margin-bottom:8px">AI code review &amp; issue automation</div>
                        <span class="ftag"><i class="fa-solid fa-check me-1"></i>Connected</span>
                     </div>
                  </div>
                  <div class="col-6 col-md-4">
                     <div class="gc p-4 text-center h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,102,0,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="fa-solid fa-bolt fa-xl" style="color:#ff6600"></i></div>
                        <div class="fw-semibold mb-1">Zapier</div>
                        <div style="font-size:.78rem;color:var(--tx3);margin-bottom:8px">Trigger automations across 5000+ apps</div>
                        <span class="ftag"><i class="fa-solid fa-check me-1"></i>Connected</span>
                     </div>
                  </div>
                  <div class="col-6 col-md-4">
                     <div class="gc p-4 text-center h-100">
                        <div style="width:52px;height:52px;border-radius:14px;background:rgba(52,168,83,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="fa-brands fa-google-drive fa-xl" style="color:#34d399"></i></div>
                        <div class="fw-semibold mb-1">Google Drive</div>
                        <div style="font-size:.78rem;color:var(--tx3);margin-bottom:8px">AI reads &amp; writes your documents</div>
                        <span class="ftag"><i class="fa-solid fa-check me-1"></i>Connected</span>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- PRICING -->
         <section id="pricing" class="sp position-relative" style="background:var(--bg2)">
            <div class="aur aur-a" style="top:50%;left:50%;transform:translate(-50%,-50%)"></div>
            <div class="container position-relative" style="z-index:1">
               <div class="text-center mb-5 rv">
                  <span class="slbl">Pricing</span>
                  <h2 class="stitle">Simple, <span class="gt">transparent pricing</span></h2>
                  <p class="ssub mx-auto">Start for free, scale as you grow. No hidden fees, no surprises.</p>
               </div>
               <div class="d-flex align-items-center justify-content-center gap-3 mb-5 rv">
                  <span style="font-size:.9rem;color:var(--tx2);font-weight:500">Monthly</span>
                  <div style="position:relative;width:52px;height:28px"><input type="checkbox" id="ptog" style="position:absolute;opacity:0;width:0;height:0"><label for="ptog" id="ptogLabel" style="position:absolute;inset:0;background:var(--sf);border:1px solid var(--bd2);border-radius:100px;cursor:pointer;transition:.3s"><span id="ptogThumb" style="position:absolute;width:20px;height:20px;left:3px;top:3px;background:var(--grad);border-radius:50%;transition:.3s;display:block"></span></label></div>
                  <span style="font-size:.9rem;color:var(--tx2);font-weight:500">Yearly</span>
                  <span style="background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.25);color:#34d399;font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:100px"><i class="fa-solid fa-tag me-1"></i>Save 30%</span>
               </div>
               <div class="row g-4 align-items-start rv">
                  <div class="col-md-4">
                     <div class="pcard h-100">
                        <div style="font-size:.82rem;font-weight:600;color:var(--tx3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">Starter</div>
                        <div class="pamt mb-1"><sup>$</sup><span class="pv" data-m="29" data-y="19">29</span></div>
                        <div style="font-size:.82rem;color:var(--tx3)" class="pper">per month, billed monthly</div>
                        <p style="font-size:.875rem;color:var(--tx2);margin:14px 0 20px;padding-bottom:20px;border-bottom:1px solid var(--bd)">Perfect for small teams getting started with AI automation.</p>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>1,000 AI conversations/mo</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>3 AI agents</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>50+ integrations</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>Basic analytics</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>Email support</div>
                        <button class="boc btn w-100 py-2 mt-4" data-bs-toggle="offcanvas" data-bs-target="#lofc" onclick="swTab('signup')">Start Free Trial</button>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="pcard pop h-100">
                        <span class="pbadge"><i class="fa-solid fa-star me-1"></i>Most Popular</span>
                        <div style="font-size:.82rem;font-weight:600;color:var(--tx3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">Pro</div>
                        <div class="pamt mb-1"><sup>$</sup><span class="pv" data-m="79" data-y="55">79</span></div>
                        <div style="font-size:.82rem;color:var(--tx3)" class="pper">per month, billed monthly</div>
                        <p style="font-size:.875rem;color:var(--tx2);margin:14px 0 20px;padding-bottom:20px;border-bottom:1px solid var(--bd)">For growing teams that need powerful automation and analytics.</p>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>Unlimited AI conversations</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>20 AI agents</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>1,000+ integrations</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>Advanced analytics + API</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>Priority 24/7 support</div>
                        <button class="bgrd btn w-100 py-2 mt-4" data-bs-toggle="offcanvas" data-bs-target="#lofc" onclick="swTab('signup')">Get Started <i class="fa-solid fa-arrow-right ms-1 fa-sm"></i></button>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="pcard h-100">
                        <div style="font-size:.82rem;font-weight:600;color:var(--tx3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">Enterprise</div>
                        <div style="font-size:2.2rem;font-weight:700;margin-bottom:4px;padding-top:4px">Custom</div>
                        <div style="font-size:.82rem;color:var(--tx3)">contact us for pricing</div>
                        <p style="font-size:.875rem;color:var(--tx2);margin:14px 0 20px;padding-bottom:20px;border-bottom:1px solid var(--bd)">For large teams with advanced security and compliance needs.</p>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>Unlimited everything</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>Custom AI fine-tuning</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>SSO &amp; SAML</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>99.99% SLA + dedicated manager</div>
                        <div class="pfl"><span class="pchk"><i class="fa-solid fa-check"></i></span>On-premises deployment</div>
                        <button class="boc btn w-100 py-2 mt-4">Talk to Sales</button>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- TESTIMONIALS -->
         <section id="testimonials" class="sp">
            <div class="container">
               <div class="text-center mb-5 rv">
                  <span class="slbl">Testimonials</span>
                  <h2 class="stitle">Loved by <span class="gt">10,000+ teams</span></h2>
               </div>
               <div class="row g-3">
                  <div class="col-md-4 rv">
                     <div class="gc p-4 h-100">
                        <div class="d-flex gap-1 mb-3"><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i></div>
                        <p style="font-size:.875rem;color:var(--tx2);line-height:1.65;font-style:italic;margin-bottom:18px">"InvoSync Jo reduced our support ticket volume by 78% in the first month. It's like hiring a 50-person support team overnight."</p>
                        <div class="d-flex align-items-center gap-2">
                           <img class="tav" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&crop=face" alt="Sarah">
                           <div>
                              <div style="font-size:.88rem;font-weight:600">Sarah Chen</div>
                              <div style="font-size:.76rem;color:var(--tx3)">Head of CX, Stripe</div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.1s">
                     <div class="gc p-4 h-100">
                        <div class="d-flex gap-1 mb-3"><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i></div>
                        <p style="font-size:.875rem;color:var(--tx2);line-height:1.65;font-style:italic;margin-bottom:18px">"Response quality is genuinely better than most human agents. The Slack integration means our AI always has full context."</p>
                        <div class="d-flex align-items-center gap-2">
                           <img class="tav" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop&crop=face" alt="Marcus">
                           <div>
                              <div style="font-size:.88rem;font-weight:600">Marcus Johnson</div>
                              <div style="font-size:.76rem;color:var(--tx3)">CTO, Linear</div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-4 rv" style="transition-delay:.2s">
                     <div class="gc p-4 h-100">
                        <div class="d-flex gap-1 mb-3"><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i><i class="fa-solid fa-star text-warning"></i></div>
                        <p style="font-size:.875rem;color:var(--tx2);line-height:1.65;font-style:italic;margin-bottom:18px">"$18K saved in the first month just from automated support. The ROI was immediate and staggering."</p>
                        <div class="d-flex align-items-center gap-2">
                           <img class="tav" src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80&h=80&fit=crop&crop=face" alt="Priya">
                           <div>
                              <div style="font-size:.88rem;font-weight:600">Priya Patel</div>
                              <div style="font-size:.76rem;color:var(--tx3)">COO, Vercel</div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- FAQ -->
         <section id="faq" class="sp" style="background:var(--bg2)">
            <div class="container">
               <div class="text-center mb-5 rv">
                  <span class="slbl">FAQ</span>
                  <h2 class="stitle">Common <span class="gt">questions</span></h2>
               </div>
               <div class="row justify-content-center rv">
                  <div class="col-lg-8">
                     <div class="accordion acco" id="faqAcc">
                        <div class="accordion-item">
                           <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#f1">How long does it take to set up InvoSync Jo?</button></h2>
                           <div id="f1" class="accordion-collapse collapse show" data-bs-parent="#faqAcc">
                              <div class="accordion-body">Most teams are fully live within 24 hours. Connect your tools, upload your documentation, and your AI agents are ready. No engineering work required.</div>
                           </div>
                        </div>
                        <div class="accordion-item">
                           <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f2">What AI models does InvoSync Jo use?</button></h2>
                           <div id="f2" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                              <div class="accordion-body">We support GPT-4o, Claude 3.5 Sonnet, Gemini Pro, and Llama 3. You choose the model per agent based on cost and performance requirements.</div>
                           </div>
                        </div>
                        <div class="accordion-item">
                           <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f3">Is my data secure and private?</button></h2>
                           <div id="f3" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                              <div class="accordion-body">Absolutely. All data is encrypted at rest and in transit. We are SOC 2 Type II certified and GDPR compliant. Your data is never used to train AI models.</div>
                           </div>
                        </div>
                        <div class="accordion-item">
                           <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f4">Do you offer a free trial?</button></h2>
                           <div id="f4" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                              <div class="accordion-body">Yes � 14 days free on any plan, no credit card required. You get access to all features. If you're not satisfied, we'll give you a full refund, no questions asked.</div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- FINAL CTA -->
         <section id="cta">
            <div class="aur aur-a" style="top:50%;left:50%;transform:translate(-50%,-50%)"></div>
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:400px;background:radial-gradient(ellipse,rgba(139,92,246,.2),transparent 70%);pointer-events:none"></div>
            <div class="container position-relative" style="z-index:2">
               <div class="rv">
                  <span class="hbadge mb-4 d-inline-flex"><span class="bdot"></span>14-day free trial � No credit card required</span>
                  <h2 style="font-size:clamp(2rem,5vw,3.8rem);font-weight:700;letter-spacing:-.025em;line-height:1.1;margin-bottom:18px">Ready to automate your<br><span class="gt">entire business?</span></h2>
                  <p style="font-size:1.05rem;color:var(--tx2);max-width:500px;margin:0 auto 36px">Join 50,000+ teams using InvoSync Jo to save time, cut costs, and deliver exceptional customer experiences.</p>
                  <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                     <button class="bgrd btn px-4 py-3 fs-6" data-bs-toggle="offcanvas" data-bs-target="#lofc" onclick="swTab('signup')">Start Free Today <i class="fa-solid fa-arrow-right ms-1"></i></button>
                     <button class="boc btn px-4 py-3 fs-6"><i class="fa-regular fa-comment-dots me-2"></i>Talk to Sales</button>
                  </div>
               </div>
            </div>
         </section>
@endsection

