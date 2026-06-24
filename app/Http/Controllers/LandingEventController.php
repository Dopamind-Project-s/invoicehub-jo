<?php
namespace App\Http\Controllers;
use App\Services\Landing\LandingAnalyticsService; use Illuminate\Http\RedirectResponse; use Illuminate\Http\Request;
class LandingEventController extends Controller { public function cta(Request $request, LandingAnalyticsService $analytics): RedirectResponse { $analytics->record($request,'cta_click',['target'=>$request->input('target')]); return redirect()->to($request->input('target') ?: route('login')); } public function pricing(Request $request, LandingAnalyticsService $analytics): RedirectResponse { $analytics->record($request,'pricing_click',['plan_id'=>$request->input('plan_id')]); return redirect()->to($request->input('target') ?: route('login')); }}
