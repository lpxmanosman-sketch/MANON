<?php
/**
 * About MANON Page
 */
$pageTitle = 'عن منون | قصة الفخامة والأناقة الملكية';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<div class="py-5 text-center" style="background: linear-gradient(135deg, #F9F6F0 0%, #EDE5D8 100%); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <span class="section-tag">OUR STORY & HERITAGE</span>
        <h1 class="display-5 fw-bold text-dark mb-2">عن منون | MANON</h1>
        <p class="text-muted fs-5 max-w-600 mx-auto">
            عبايتك فخامة تليق بك • حين تلتقي أصالة الهيبة بلمسات الموضة المعاصرة
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <span class="section-tag">WHO WE ARE</span>
            <h2 class="section-title mb-4">قصة ولدت من شغف الفخامة والتميز</h2>
            <p class="lh-lg text-muted fs-6 mb-3">
                انطلقت علامة <strong>منون (MANON)</strong> لتكون الوجهة الأولى لكل سيدة تبحث عن التميز والرقي. نحن نؤمن بأن العباية ليست مجرد رداء، بل هي تعبير عن الهوية، الوقار، والذوق الرفيع.
            </p>
            <p class="lh-lg text-muted fs-6 mb-4">
                في كل قطعة نصنعها، نمزج بين خطوط الموضة العالمية المحتشمة وأصالة العباية الخليجية، مستخدمين أجود أنواع الأقمشة المستوردة خصيصاً كالكريب الملكي الياباني والحرير الكوري البارد فاحم السواد المقاوم للتجعد.
            </p>
            <div class="p-3 rounded-3 border-start border-4 border-warning bg-white shadow-sm">
                <p class="fw-bold text-dark mb-0 fs-5">"في منون، لا نصنع عباءات تقليدية، بل نصوغ قطعاً فنية تمنحك حضوراً آسراً في كل خطوة."</p>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rounded-4 overflow-hidden shadow-lg border p-2 bg-white">
                <img src="assets/images/hero.jpg" alt="About MANON" class="w-100 rounded-3" style="max-height: 480px; object-fit: cover;">
            </div>
        </div>
    </div>

    <!-- Pillars / Values -->
    <div class="row g-4 my-5">
        <div class="col-12 text-center mb-2">
            <span class="section-tag">OUR VALUES</span>
            <h3 class="section-title">ركائز الجودة في منون</h3>
        </div>

        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm border h-100 text-center">
                <div class="feature-icon-wrap mb-3 mx-auto">
                    <i class="bi bi-gem"></i>
                </div>
                <h4 class="fw-bold mb-2">السواد الفاحم الأصيل</h4>
                <p class="text-muted small lh-lg">نختار أقمشة صُممت بتقنيات نسيج متطورة تحتفظ ببريقها وسوادها الملكي الفاحم حتى مع كثرة الاستعمال والغسيل.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm border h-100 text-center">
                <div class="feature-icon-wrap mb-3 mx-auto">
                    <i class="bi bi-scissors"></i>
                </div>
                <h4 class="fw-bold mb-2">خياطة يدوية متناهية الدقة</h4>
                <p class="text-muted small lh-lg">يعمل في مشاغل منون أمهر الخياطين والمطرزين لضمان استقامة الخياطة وتناغم التطريز دون أي عيوب ظاهرة أو خفية.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm border h-100 text-center">
                <div class="feature-icon-wrap mb-3 mx-auto">
                    <i class="bi bi-heart-pulse"></i>
                </div>
                <h4 class="fw-bold mb-2">تجربة تسوق ملكية</h4>
                <p class="text-muted small lh-lg">من لحظة تصفحكِ للموقع حتى فتح صندوق العباية المعطر بالبخور الملكي، نحرص على تقديم تجربة استثنائية تليق بكِ.</p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="p-5 rounded-4 text-center text-white mt-5" style="background: linear-gradient(135deg, #18181B 0%, #2A2521 100%);">
        <h2 class="fw-bold mb-3">هل أنتِ مستعدة لاكتشاف عبايتكِ القادمة؟</h2>
        <p class="text-light opacity-75 fs-6 mb-4 max-w-600 mx-auto">تصفحي تشكيلاتنا المتجددة واختاري ما يبرز جمالك وفخامتك اليومية أو لمناسباتكِ الخاصة.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="shop.php" class="btn btn-manon-gold btn-lg">تسوقي الكولكشن الآن</a>
            <a href="https://wa.me/<?= WHATSAPP_PHONE ?>" target="_blank" class="btn btn-whatsapp-order btn-lg">
                <i class="bi bi-whatsapp"></i> استفسار عبر واتساب
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
