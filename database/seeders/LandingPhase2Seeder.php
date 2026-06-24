<?php
namespace Database\Seeders;
use App\Models\{LandingHeroSlide,LandingIntegration,LandingPartner,LandingStatistic,LandingTestimonial}; use Illuminate\Database\Seeder;
class LandingPhase2Seeder extends Seeder { public function run(): void {
LandingHeroSlide::updateOrCreate(['title_ar'=>'أدر فواتير منشأتك بثقة مع InvoSync'],['subtitle_ar'=>'منصة عربية لإدارة الفواتير والعملاء والمنتجات وتجهيز الربط مع نظام الفوترة الوطني.','primary_cta_text_ar'=>'ابدأ إدارة فواتير منشأتك','primary_cta_url'=>'/login','secondary_cta_text_ar'=>'تواصل عبر واتساب','secondary_cta_url'=>'https://wa.me/962776079926','sort_order'=>1,'is_active'=>true]);
LandingTestimonial::updateOrCreate(['name'=>'مصعب الزعبي'],['establishment'=>'دوبامايند للتحول الرقمي','position'=>'مؤسس','testimonial_ar'=>'InvoSync يجعل تجربة الفوترة الإلكترونية أكثر وضوحاً وسهولة للمنشآت.','rating'=>5,'sort_order'=>1,'is_active'=>true]);
foreach ([['جوفوتارا','JoFotara','الربط مع نظام الفوترة الوطني','✓','available'],['واتساب','WhatsApp','مشاركة الفواتير مع العملاء','☏','available'],['PDF','PDF','تصدير قوالب فواتير احترافية','PDF','available'],['Excel','Excel','استيراد وتصدير البيانات','XLS','available'],['API','API','واجهة برمجية للتكامل','API','coming_soon'],['POS','POS','تكامل نقاط البيع','POS','coming_soon'],['ERP','ERP','تكامل أنظمة ERP','ERP','coming_soon']] as $i=>$r) LandingIntegration::updateOrCreate(['name_ar'=>$r[0]],['name_en'=>$r[1],'description_ar'=>$r[2],'icon'=>$r[3],'status'=>$r[4],'sort_order'=>$i+1,'is_active'=>true]);
foreach ([['فاتورة إلكترونية','50,000','+','🧾'],['منشأة قابلة للإدارة','1,000','+','🏢'],['جاهزية النظام','99.9','%','⚡'],['قوالب PDF احترافية','5','+','📄']] as $i=>$r) LandingStatistic::updateOrCreate(['label_ar'=>$r[0]],['value'=>$r[1],'suffix'=>$r[2],'icon'=>$r[3],'sort_order'=>$i+1,'is_active'=>true]);
foreach (['دوبامايند للتحول الرقمي','InvoSync','JoFotara Integration'] as $i=>$n) LandingPartner::updateOrCreate(['name_ar'=>$n],['sort_order'=>$i+1,'is_active'=>true]);
}}
