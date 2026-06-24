<?php
namespace App\Http\Controllers\Admin\LandingCms;
use App\Http\Controllers\Controller; use App\Models\{LandingEvent,LandingHeroSlide,LandingIntegration,LandingPartner,LandingStatistic,LandingTestimonial,SiteSetting}; use App\Services\Landing\LandingAnalyticsService; use Illuminate\Http\Request; use Illuminate\Support\Facades\Validator;
class GenericLandingController extends Controller {
 public function analytics(LandingAnalyticsService $a){return view('admin.landing-cms.analytics',['counters'=>$a->counters(),'events'=>LandingEvent::latest('created_at')->limit(30)->get(),'referrers'=>LandingEvent::selectRaw('referrer,count(*) as total')->whereNotNull('referrer')->groupBy('referrer')->orderByDesc('total')->limit(10)->get()]);}
 public function seo(){return view('admin.landing-cms.settings.edit',['settings'=>SiteSetting::where('group','seo')->pluck('value','key')->all(),'group'=>'seo']);}
 public function theme(){return view('admin.landing-cms.settings.edit',['settings'=>SiteSetting::where('group','theme')->pluck('value','key')->all(),'group'=>'theme']);}
 public function saveGroup(Request $r,string $group){ if($group==='seo' && $r->filled('schema_json_ld')) Validator::make($r->all(),['schema_json_ld'=>'json'])->validate(); foreach($r->except(['_token','_method']) as $k=>$v) SiteSetting::updateOrCreate(['group'=>$group,'key'=>$k,'locale'=>null],['value'=>$v,'type'=>'text','is_public'=>true]); return back()->with('status','تم الحفظ.');}
}
