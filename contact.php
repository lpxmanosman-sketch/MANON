<?php
/**
 * Contact Us Page - MANON
 */
$pageTitle = 'تواصل معنا | خدمة عملاء منون';
require_once __DIR__ . '/includes/header.php';

$sentSuccess = false;
$msgError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderName = trim($_POST['name'] ?? '');
    $senderPhone = trim($_POST['phone'] ?? '');
    $senderSubject = trim($_POST['subject'] ?? '');
    $senderMsg = trim($_POST['message'] ?? '');

    if (empty($senderName) || empty($senderPhone) || empty($senderMsg)) {
        $msgError = 'يرجى ملء جميع الحقول المطلوبة.';
    } else {
        $sentSuccess = true;
    }
}
?>

<div class="py-5 text-center" style="background: linear-gradient(135deg, #F8F3EC 0%, #EDE4D8 100%); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <span class="section-tag">WE ARE HERE FOR YOU</span>
        <h1 class="display-5 fw-bold text-dark mb-2">تواصل معنا</h1>
        <p class="text-muted fs-6 max-w-600 mx-auto">
            يسعدنا الإجابة عن كافة استفساراتكِ ومساعدتكِ في اختيار العباية والمقاس المثالي.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Contact Info Cards -->
        <div class="col-lg-5">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border border-secondary-subtle mb-4">
                <h4 class="fw-bold mb-4 pb-2 border-bottom">قنوات التواصل المباشر</h4>

                <!-- WhatsApp -->
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="feature-icon-wrap p-0" style="width: 50px; height: 50px; min-width: 50px; font-size: 1.4rem;">
                        <i class="bi bi-whatsapp text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">الواتساب الرسمي (خدمة العملاء)</h6>
                        <p class="text-muted small mb-2">رد فوري على مدار 24 ساعة لجميع الطلبات والاستفسارات</p>
                        <a href="https://wa.me/<?= WHATSAPP_PHONE ?>" target="_blank" class="btn btn-sm btn-whatsapp-order">
                            <i class="bi bi-whatsapp me-1"></i> <?= WHATSAPP_RAW ?>
                        </a>
                    </div>
                </div>

                <!-- Phone -->
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="feature-icon-wrap p-0" style="width: 50px; height: 50px; min-width: 50px; font-size: 1.4rem;">
                        <i class="bi bi-telephone text-warning"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">الهاتف المباشر</h6>
                        <p class="text-muted small mb-1">متاح يومياً من 10 صباحاً حتى 11 مساءً</p>
                        <strong class="text-dark dir-ltr d-inline-block"><?= WHATSAPP_RAW ?></strong>
                    </div>
                </div>

                <!-- Email -->
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="feature-icon-wrap p-0" style="width: 50px; height: 50px; min-width: 50px; font-size: 1.4rem;">
                        <i class="bi bi-envelope text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">البريد الإلكتروني</h6>
                        <p class="text-muted small mb-1">للاستفسارات والتعاون التجاري</p>
                        <strong class="text-dark"><?= STORE_EMAIL ?></strong>
                    </div>
                </div>

                <!-- Location -->
                <div class="d-flex align-items-start gap-3">
                    <div class="feature-icon-wrap p-0" style="width: 50px; height: 50px; min-width: 50px; font-size: 1.4rem;">
                        <i class="bi bi-geo-alt text-danger"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">المقر الرئيسي</h6>
                        <p class="text-muted small mb-0">القاهرة - جمهورية مصر العربية (شحن لجميع المحافظات والدول العربية)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border border-secondary-subtle">
                <h4 class="fw-bold mb-2">أرسلي لنا رسالة</h4>
                <p class="text-muted small mb-4">سيتواصل معكِ فريق خدمة عملاء منون في أقرب وقت ممكن</p>

                <?php if ($sentSuccess): ?>
                    <div class="alert alert-success p-4 rounded-3 text-center mb-4">
                        <i class="bi bi-check-circle-fill fs-2 d-block mb-2"></i>
                        <h5 class="fw-bold">تم استلام رسالتكِ بنجاح!</h5>
                        <p class="small mb-0">شكراً لتواصلكِ مع منون. سيقوم فريقنا بالرد عليكِ خلال دقائق معدودة.</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($msgError)): ?>
                    <div class="alert alert-danger rounded-3 mb-4"><?= e($msgError) ?></div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">الاسم الكريم <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg" placeholder="اسمكِ" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">رقم الهاتف / الواتساب <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control form-control-lg" placeholder="010..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">موضوع الرسالة</label>
                            <input type="text" name="subject" class="form-control form-control-lg" placeholder="استفسار عن مقاس، طلب خاص، موعد شحن...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">نص الرسالة <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="4" placeholder="اكتبي استفساركِ هنا..." required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-manon-primary btn-lg w-100">
                                <i class="bi bi-send me-2"></i> إرسال الرسالة
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="row mt-5 pt-4">
        <div class="col-12">
            <div class="section-header text-center">
                <span class="section-tag">FAQS</span>
                <h3 class="section-title">الأسئلة الأكثر شيوعاً</h3>
            </div>

            <div class="accordion accordion-flush bg-white rounded-4 shadow-sm border p-3 p-md-4" id="faqAccordion">
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            كيف أعرف المقاس المناسب لقامتي؟
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small lh-lg">
                            مقاسات العبايات تعتمد بشكل رئيسي على طول قامتك بالإنش أو السنتيمتر. مقاس 52 يناسب طول 150-155 سم، ومقاس 54 يناسب 156-160 سم، ومقاس 56 يناسب 161-165 سم، ومقاس 58 يناسب 166-170 سم. يمكنكِ مراجعة جدول المقاسات في صفحة أي منتج أو استشارة فريقنا على الواتساب فوراً.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            هل تتوفر خدمة المعاينة والقياس عند الاستلام؟
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small lh-lg">
                            نعم بكل تأكيد! يحق لكِ معاينة العباية والتأكد من جودة الخامة والمقاس بحضور مندوب التوصيل قبل دفع الحساب.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            كم تستغرق مدة توصيل الطلب؟
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small lh-lg">
                            يستغرق الشحن داخل القاهرة والجيزة من 24 إلى 48 ساعة فقط، ولباقي محافظات مصر من 2 إلى 3 أيام عمل كحد أقصى.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            هل تأتي العباية مع طرحة مجانية؟
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small lh-lg">
                            نعم، جميع عبايات منون تصلكِ مرفقة بطرحة فاخرة متناسقة تماماً من قماش الشيفون الليزر الياباني كهدية مع كل طلب.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
