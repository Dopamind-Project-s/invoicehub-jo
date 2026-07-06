<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::where('role', User::ROLE_SUPER_ADMIN)->value('id') ?: User::value('id');
        $titles = [
            'ما هو نظام الفوترة الوطني الأردني؟', 'من الملزم بالتسجيل في نظام الفوترة الإلكتروني؟', 'خطوات ربط منشأتك مع نظام الفوترة الوطني', 'الفرق بين الفاتورة الإلكترونية والفاتورة التقليدية', 'كيف يساعدك InvoSync على إدارة فواتيرك وربطها مع JoFotara؟', 'أهم الأخطاء عند تطبيق الفوترة الإلكترونية وكيف تتجنبها', 'فوائد الفوترة الإلكترونية للشركات الصغيرة والمتوسطة في الأردن',
        ];
        foreach ($titles as $i => $title) {
            Blog::updateOrCreate(['slug' => 'blog-'.($i + 1).'-'.Str::slug(Str::ascii($title))], [
                'title_ar' => $title,
                'title_en' => 'Jordan e-invoicing insight '.($i + 1),
                'excerpt_ar' => 'دليل مبسط وعملي يشرح أبرز النقاط التي تحتاجها المنشآت الأردنية للامتثال للفوترة الإلكترونية بثقة وبدون تعقيد.',
                'content_ar' => "تتجه بيئة الأعمال في الأردن إلى اعتماد الفوترة الإلكترونية كجزء من التحول الرقمي والامتثال الضريبي. يساعد هذا المقال أصحاب المنشآت على فهم الفكرة الأساسية، المتطلبات العملية، وكيفية تجهيز بيانات العملاء والمنتجات والضرائب قبل الربط.\n\nتبدأ الرحلة بمراجعة بيانات المنشأة والرقم الضريبي وتسلسل مصدر الدخل، ثم اختيار آلية مناسبة لإصدار الفواتير وحفظها وإرسالها. وجود منصة مثل InvoSync يختصر الجهد من خلال تنظيم الفواتير، تتبع الحالات، وتقليل الأخطاء المتكررة في الإدخال أو التصنيف.\n\nالنصيحة الأهم هي التعامل مع الفوترة الإلكترونية كمشروع تشغيلي مستمر وليس كإجراء لمرة واحدة: درّب الفريق، راجع الصلاحيات، واختبر دورة الفاتورة من الإنشاء حتى الإرسال والمتابعة.",
                'category' => $i % 2 ? 'الفوترة الإلكترونية' : 'JoFotara',
                'tags' => ['الفوترة الأردنية', 'InvoSync', 'JoFotara'],
                'status' => Blog::STATUS_PUBLISHED,
                'is_featured' => $i === 0,
                'published_at' => now()->subDays(7 - $i),
                'image' => ['assets/img/fawtara.png','assets/img/fawtara2.png','assets/img/fawtara3.png','assets/img/fawtara4.png'][$i % 4],
                'meta_title' => $title.' | InvoSync',
                'meta_description' => 'مقال عربي أصلي من InvoSync حول الفوترة الإلكترونية ونظام الفوترة الوطني الأردني.',
                'created_by' => $authorId,
            ]);
        }
    }
}
