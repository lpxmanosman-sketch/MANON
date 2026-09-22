<?php
/**
 * MANON - Luxury Abayas Storefront Home
 */
$pageTitle = 'الرئيسية | منون للأزياء والعبايات الفاخرة';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// 1. Fetch Categories
$catStmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 6");
$categories = $catStmt->fetchAll();

// 2. Fetch Featured Products
$featStmt = $db->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 AND p.is_featured = 1 
    ORDER BY p.id DESC LIMIT 4
");
$featuredProducts = $featStmt->fetchAll();

// 3. Fetch New Arrivals
$newStmt = $db->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 AND p.is_new = 1 
    ORDER BY p.id DESC LIMIT 4
");
$newArrivals = $newStmt->fetchAll();

// 4. Fetch Best Selling / All
$bestStmt = $db->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 
    ORDER BY p.views_count DESC, p.id DESC LIMIT 4
");
$bestSelling = $bestStmt->fetchAll();

// 5. Fetch Active Colors & Approved Reviews
$activeColors = getActiveColors();
$reviewsStmt = $db->query("SELECT * FROM reviews WHERE status = 'approved' ORDER BY id DESC LIMIT 6");
$approvedReviews = $reviewsStmt->fetchAll();
$wheelEnabled = getSetting('wheel_enabled', '1');
?>

<!-- 1. HERO SECTION -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-subtitle-tag">HAUTE COUTURE ABAYAS</span>
                <h1 class="hero-title">
                    <span class="brand-accent">MANON</span><br>
                    عبايتك فخامة تليق بك
                </h1>
                <p class="hero-slogan">حين تتحدث الأناقة بلغة الفخامة الهادئة والذوق الرفيع</p>
                <p class="hero-desc">
                    نقدم لكِ في منون أرقى تصاميم العبايات الخليجية المعاصرة المصممة يدوياً بأجود خامات الكريب والحرير الياباني والكوري، لتعكس هيبتك وحضورك الاستثنائي في كل مناسبة.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="shop.php" class="btn btn-manon-primary btn-lg">
                        <span>تسوقي الآن</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <a href="categories.php" class="btn btn-manon-outline btn-lg">
                        <span>استكشفي التصنيفات</span>
                    </a>
                </div>

                <!-- Trust stats -->
                <div class="row mt-5 pt-4 border-top border-secondary-subtle">
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-dark">+10,000</div>
                        <div class="small text-muted">عميلة تثق بنا</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-dark">100%</div>
                        <div class="small text-muted">أقمشة أصلية فاخرة</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-dark">48 ساعة</div>
                        <div class="small text-muted">توصيل سريع</div>
                    </div>
                </div>
            </div>

            <!-- Hero Image Showcase -->
            <div class="col-lg-6">
                <div class="hero-image-box">
                    <img src="assets/images/hero.jpg" alt="MANON Luxury Abaya">
                    <div class="hero-float-badge">
                        <div class="badge-icon">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark mb-0">كولكشن جديد 2026</div>
                            <div class="small text-muted">إصدار حصري محدود</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 1.5 INTRODUCTION SECTION (مقدمة) -->
<section class="py-5 bg-white intro-section border-bottom border-secondary-subtle">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-tag">من نحن</span>
                <h2 class="section-title mb-3">مقدمة</h2>
                <div style="width: 50px; height: 3px; background: #C5A880; margin-bottom: 24px;"></div>
                <p class="fs-5 text-dark lh-lg mb-4" style="font-weight: 500;">
                    في منون، نصمم عبايات تجمع بين الأناقة والراحة، مع اهتمام خاص بجودة الخامات والتفاصيل التي تمنح كل قطعة طابعاً مميزاً.
                </p>
                <p class="text-muted lh-base mb-4">
                    انطلقت علامة منون لتلبي شغف المرأة العصرية بإطلالة فريدة تجسد الهوية الخليجية والعربية بلمسات حداثية عالمية. نحرص في كل قطعة على تحقيق التوازن المثالي بين الفخامة والراحة العملية لكل يوم ومناسبة.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="shop.php" class="btn btn-manon-primary">
                        <span>اكتشفي المجموعة الكاملة</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <a href="#special-order-section" class="btn btn-manon-outline">
                        <span>طلب تفصيل خاص</span>
                        <i class="bi bi-scissors"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="assets/images/cat2.jpg" alt="MANON Elegance" class="img-fluid rounded-4 shadow-sm w-100" style="max-height: 440px; object-fit: cover;">
                    <div class="position-absolute bottom-0 start-0 m-4 p-3 bg-white bg-opacity-95 rounded-3 shadow-sm border border-light">
                        <span class="text-manon-gold fw-bold d-block fs-5">MANON ATELIER</span>
                        <span class="small text-muted">فخامة التفاصيل وأناقة الحضور</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. CATEGORIES SECTION -->
<section class="py-5 mt-4">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">COLLECTIONS</span>
            <h2 class="section-title">تصنيفات منون الراقية</h2>
            <p class="section-desc">اختاري ما يناسب مناسبتك وأسلوبك من مجموعاتنا المبتكرة بعناية فائقة</p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-4 col-md-6">
                    <a href="shop.php?category=<?= e($cat['slug']) ?>" class="category-card">
                        <img src="<?= e($cat['image'] ?: 'assets/images/cat1.jpg') ?>" alt="<?= e($cat['name']) ?>">
                        <div class="category-overlay"></div>
                        <div class="category-info">
                            <h3 class="category-name"><?= e($cat['name']) ?></h3>
                            <span class="category-link">
                                <span>تصفحي المجموعة</span>
                                <i class="bi bi-arrow-left"></i>
                            </span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 3. FEATURED PRODUCTS -->
<section class="py-5" style="background: #F5EFEB;">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="section-tag">EXCLUSIVE PICKS</span>
                <h2 class="section-title mb-0">مختارات منون المميزة</h2>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="shop.php?filter=featured" class="btn btn-manon-outline">
                    <span>عرض الكل</span>
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredProducts as $prod): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="product-card">
                        <div class="product-thumb">
                            <a href="product.php?id=<?= $prod['id'] ?>">
                                <img src="<?= e($prod['main_image']) ?>" alt="<?= e($prod['name']) ?>">
                            </a>
                            <?php if ($prod['sale_price']): ?>
                                <span class="product-badge sale">خصم خاص</span>
                            <?php elseif ($prod['is_new']): ?>
                                <span class="product-badge new">جديد</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <span class="product-category"><?= e($prod['category_name']) ?></span>
                            <h3 class="product-title">
                                <a href="product.php?id=<?= $prod['id'] ?>"><?= e($prod['name']) ?></a>
                            </h3>
                            <div class="product-price-box">
                                <?php if ($prod['sale_price']): ?>
                                    <span class="product-price text-danger"><?= formatPrice($prod['sale_price']) ?></span>
                                    <span class="product-old-price"><?= formatPrice($prod['price']) ?></span>
                                <?php else: ?>
                                    <span class="product-price"><?= formatPrice($prod['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-card-actions">
                                <button type="button" class="btn-add-cart btn-add-cart-ajax" data-id="<?= $prod['id'] ?>">
                                    <i class="bi bi-bag-plus"></i> أضف للسلة
                                </button>
                                <a href="product.php?id=<?= $prod['id'] ?>" class="btn-view-details" title="عرض التفاصيل">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4. PROMO BANNER -->
<div class="container">
    <div class="promo-banner">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="promo-tag">LIMITED TIME OFFER</span>
                <h2 class="promo-title">تألقي بعباية تليق بفخامتك مع باقة هدايا خاصة</h2>
                <p class="text-light opacity-75 fs-6 mb-4">
                    اطلبي الآن واحصلي على طرحة شيفون ليزر يابانية مجاناً وتغليف هدايا ملكي أنيق مع كل طلب عبر متجر منون.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="shop.php" class="btn btn-manon-gold">تسوقي العروض الآن</a>
                    <a href="https://wa.me/<?= WHATSAPP_PHONE ?>" target="_blank" class="btn btn-whatsapp-order">
                        <i class="bi bi-whatsapp"></i> استشارة تفصيل عبر واتساب
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-center">
                <img src="assets/images/logo.jpg" alt="MANON" class="img-fluid rounded-4 shadow-lg p-2 bg-white" style="max-height: 220px;">
            </div>
        </div>
    </div>
</div>

<!-- 5. NEW ARRIVALS -->
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="section-tag">FRESH OFF THE ATELIER</span>
                <h2 class="section-title mb-0">أحدث العبايات وصولاً</h2>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="shop.php?filter=new" class="btn btn-manon-outline">
                    <span>عرض كل الجديد</span>
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($newArrivals as $prod): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="product-card">
                        <div class="product-thumb">
                            <a href="product.php?id=<?= $prod['id'] ?>">
                                <img src="<?= e($prod['main_image']) ?>" alt="<?= e($prod['name']) ?>">
                            </a>
                            <span class="product-badge new">وصل حديثاً</span>
                        </div>
                        <div class="product-content">
                            <span class="product-category"><?= e($prod['category_name']) ?></span>
                            <h3 class="product-title">
                                <a href="product.php?id=<?= $prod['id'] ?>"><?= e($prod['name']) ?></a>
                            </h3>
                            <div class="product-price-box">
                                <?php if ($prod['sale_price']): ?>
                                    <span class="product-price text-danger"><?= formatPrice($prod['sale_price']) ?></span>
                                    <span class="product-old-price"><?= formatPrice($prod['price']) ?></span>
                                <?php else: ?>
                                    <span class="product-price"><?= formatPrice($prod['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-card-actions">
                                <button type="button" class="btn-add-cart btn-add-cart-ajax" data-id="<?= $prod['id'] ?>">
                                    <i class="bi bi-bag-plus"></i> أضف للسلة
                                </button>
                                <a href="product.php?id=<?= $prod['id'] ?>" class="btn-view-details" title="عرض التفاصيل">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 6. WHY MANON SECTION (لماذا مانون؟) -->
<section class="features-section">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-tag">THE MANON TOUCH</span>
            <h2 class="section-title">لماذا مانون؟</h2>
            <p class="section-desc">نحن نؤمن بأن كل امرأة تستحق أن تشعر بالثقة والفخامة الاستثنائية</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="why-manon-card h-100 p-4 rounded-4 bg-white border border-secondary-subtle text-center shadow-sm">
                    <div class="why-icon-circle mx-auto">
                        <i class="bi bi-gem"></i>
                    </div>
                    <h3 class="why-benefit-title">خامات مختارة بعناية</h3>
                    <p class="text-muted small mb-0">ننتقي أجود الأقمشة الفاخرة لضمان النعومة والمظهر الملكي الدائم وثبات اللون الفاحم.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="why-manon-card h-100 p-4 rounded-4 bg-white border border-secondary-subtle text-center shadow-sm">
                    <div class="why-icon-circle mx-auto">
                        <i class="bi bi-feather"></i>
                    </div>
                    <h3 class="why-benefit-title">تصميمات مريحة وأنيقة</h3>
                    <p class="text-muted small mb-0">قصات عصرية تمنحكِ الراحة التامة والانسيابية مع الحفاظ على الهيبة والرقي في كل ظهور.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="why-manon-card h-100 p-4 rounded-4 bg-white border border-secondary-subtle text-center shadow-sm">
                    <div class="why-icon-circle mx-auto">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <h3 class="why-benefit-title">تشطيب واهتمام بالتفاصيل</h3>
                    <p class="text-muted small mb-0">خياطة دقيقة وتشطيب فائق الاحترافية يعكس أدق معايير الجودة في كل درزة وقطعة.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="why-manon-card h-100 p-4 rounded-4 bg-white border border-secondary-subtle text-center shadow-sm">
                    <div class="why-icon-circle mx-auto">
                        <i class="bi bi-scissors"></i>
                    </div>
                    <h3 class="why-benefit-title">إمكانية تنفيذ بعض التعديلات الخاصة</h3>
                    <p class="text-muted small mb-0">مرونة في تعديل الطول أو اختيار تفاصيل خاصة لتناسب مقاسك وذوقك الخاص بدقة متناهية.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SPECIAL ORDER SECTION (طلب خاص) -->
<section id="special-order-section" class="py-5" style="background: #FAF7F2;">
    <div class="container">
        <div class="special-order-section">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <span class="text-manon-gold fw-bold text-uppercase" style="letter-spacing: 2px;">ATELIER BESPOKE</span>
                    <h2 class="display-6 fw-bold text-white my-3">تفصيل وطلب خاص</h2>
                    <p class="text-light opacity-75 fs-6 lh-lg mb-4">
                        هل لديكِ تصميم معين في خيالكِ؟ أو ترغبين في تعديل مقاس أو تفاصيل عباية مخصصة؟ فريق مانون مستعد لتنفيذ طلبكِ بأعلى معايير الإتقان والدقة.
                    </p>
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-white bg-opacity-10 border border-light border-opacity-10">
                        <i class="bi bi-whatsapp fs-2 text-success"></i>
                        <div>
                            <div class="text-white fw-bold">تواصل مباشر وسريع</div>
                            <div class="small text-light opacity-75">سيتم تحويل طلبكِ مباشرة لمصممينا عبر واتساب</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="bg-white rounded-4 p-4 p-md-5 text-dark shadow-lg">
                        <h4 class="fw-bold mb-3 text-dark">نموذج الطلب الخاص</h4>
                        <p class="small text-muted mb-4">املئي البيانات التالية وسيتواصل معكِ فريق التصميم فوراً لتأكيد التفاصيل:</p>

                        <form id="specialOrderForm" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control" required placeholder="مثال: نورة المحمد">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">رقم الهاتف / واتساب <span class="text-danger">*</span></label>
                                    <input type="tel" name="customer_phone" class="form-control" required placeholder="01XXXXXXXXX" dir="ltr">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">اللون المفضل</label>
                                    <select name="preferred_color" class="form-select">
                                        <option value="">اختاري اللون المفضل...</option>
                                        <?php foreach ($activeColors as $col): ?>
                                            <option value="<?= e($col['name']) ?>"><?= e($col['name']) ?></option>
                                        <?php endforeach; ?>
                                        <option value="تحديد في المحادثة">تحديد اللون في المحادثة</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">المقاس المطلوب</label>
                                    <select name="size" class="form-select">
                                        <option value="52">مقاس 52</option>
                                        <option value="54" selected>مقاس 54</option>
                                        <option value="56">مقاس 56</option>
                                        <option value="58">مقاس 58</option>
                                        <option value="60">مقاس 60</option>
                                        <option value="خاص">تفصيل بمقاس خاص (تحديد عبر واتساب)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">وصف الموديل والتعديلات المطلوبة <span class="text-danger">*</span></label>
                                    <textarea name="design_description" class="form-control" rows="3" required placeholder="اشرحي لنا فكرة الموديل، القماش المفضل، الطول أو التعديلات الخاصة المطلوبة..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">صورة للموديل أو الرسم التوضيحي (اختياري)</label>
                                    <input type="file" name="model_image" class="form-control" accept="image/*">
                                    <div class="form-text small">يمكنكِ إرفاق صورة توضيحية لتصميم العباية (JPG, PNG)</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">ملاحظات إضافية</label>
                                    <input type="text" name="notes" class="form-control" placeholder="أي تفاصيل أخرى ترغبين في إضافتها...">
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-manon-primary w-100 py-3 fw-bold fs-6">
                                        <i class="bi bi-whatsapp ms-2"></i>
                                        إرسال الطلب عبر واتساب
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. CUSTOMER REVIEWS (آراء العملاء) -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="section-tag">TESTIMONIALS</span>
                <h2 class="section-title mb-1">آراء العملاء</h2>
                <p class="text-muted mb-0">تجاربكم وتعليقاتكم تهمنا 🤍</p>
            </div>
            <div class="mt-3 mt-md-0">
                <button type="button" class="btn btn-manon-outline" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                    <i class="bi bi-pencil-square ms-1"></i>
                    <span>أضف تقييمك</span>
                </button>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($approvedReviews)): ?>
                <?php foreach ($approvedReviews as $rev): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="review-card-luxury">
                            <div class="review-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $rev['rating'] ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-dark fs-6 lh-base mb-4 flex-grow-1">
                                "<?= nl2br(e($rev['review_text'])) ?>"
                            </p>
                            <div class="d-flex align-items-center gap-3 pt-3 border-top border-light-subtle">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-manon-gold" style="width: 44px; height: 44px; border: 1px solid var(--color-gold);">
                                    <?= mb_substr($rev['customer_name'], 0, 1, 'UTF-8') ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?= e($rev['customer_name']) ?></div>
                                    <div class="small text-muted">
                                        <?= e($rev['customer_city'] ?: 'عميلة مميزة') ?> 
                                        • <?= date('Y/m/d', strtotime($rev['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4 text-muted">
                    كن أول من يشاركنا رأيه وتجربته الفاخرة مع عبايات منون!
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- MODAL: ADD REVIEW -->
<div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom border-light-subtle p-4">
                <div>
                    <h5 class="modal-title fw-bold" id="addReviewModalLabel">شاركينا رأيك في منون 🤍</h5>
                    <p class="small text-muted mb-0">رأيك يسعدنا ويساعدنا دائماً على تقديم الأفضل</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customerReviewForm">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">الاسم الكريم <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control" required placeholder="اسمك الكريم">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">المدينة (اختياري)</label>
                        <input type="text" name="customer_city" class="form-control" placeholder="مثال: القاهرة، جدة، الرياض...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">التقييم بالنجوم <span class="text-danger">*</span></label>
                        <select name="rating" class="form-select" required>
                            <option value="5" selected>⭐⭐⭐⭐⭐ (5 نجوم - ممتاز جداً)</option>
                            <option value="4">⭐⭐⭐⭐ (4 نجوم - جيد جداً)</option>
                            <option value="3">⭐⭐⭐ (3 نجوم - جيد)</option>
                            <option value="2">⭐⭐ (نجمتان - مقبول)</option>
                            <option value="1">⭐ (نجمة - يحتاج تحسين)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">نص التجربة أو التقييم <span class="text-danger">*</span></label>
                        <textarea name="review_text" class="form-control" rows="4" required placeholder="اكتبي تجربتك مع خامة العباية، التوصيل، أو المعاملة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-light-subtle p-4">
                    <button type="button" class="btn btn-manon-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-manon-primary px-4">إرسال التقييم</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WHEEL OF FORTUNE MODAL (عجلة الحظ) -->
<?php if ($wheelEnabled == '1'): ?>
<div class="modal fade" id="welcomeWheelModal" tabindex="-1" aria-labelledby="welcomeWheelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg text-center overflow-hidden" style="background: #FAF7F2;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <div class="badge bg-dark text-warning px-3 py-2 rounded-pill mb-2">هدية حصرية لزوارنا ✨</div>
                <h3 class="fw-bold text-dark mb-1">عجلة الحظ من MANON</h3>
                <p class="text-muted small mb-4">لفي العجلة واحصلي على كود خصم فوري يصل إلى 10% على طلبكِ القادم!</p>

                <div class="wheel-container">
                    <div class="wheel-pointer"></div>
                    <canvas id="wheelCanvas" width="280" height="280" class="wheel-canvas"></canvas>
                    <div class="wheel-center-pin">
                        <i class="bi bi-gift-fill"></i>
                    </div>
                </div>

                <div id="wheelActionArea">
                    <button type="button" id="btnSpinWheel" class="btn btn-manon-primary px-5 py-3 fw-bold fs-6 shadow-sm">
                        <i class="bi bi-arrow-repeat ms-1"></i> تدوير العجلة الآن
                    </button>
                </div>

                <div id="wheelResultArea" class="d-none mt-3">
                    <div class="alert alert-success border-0 rounded-4 py-3 shadow-sm mb-3">
                        <h5 class="fw-bold mb-1" id="wheelWinMessage">🎉 مبروك! حصلتِ على خصم 10%</h5>
                        <div class="small text-muted">استخدمي الكود التالي عند إتمام الطلب:</div>
                        <div class="my-2">
                            <span class="coupon-code-badge" id="wheelResultCode">MANON10-XXXX</span>
                        </div>
                        <button type="button" id="btnCopyWheelCode" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                            <i class="bi bi-clipboard ms-1"></i> نسخ الكود
                        </button>
                    </div>
                    <a href="shop.php" class="btn btn-manon-gold w-100 py-2">تسوقي واستخدمي الكود الآن</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Button to Open Wheel -->
<button type="button" class="btn btn-dark shadow-lg rounded-pill position-fixed d-flex align-items-center gap-2" 
        style="bottom: 90px; right: 20px; z-index: 998; border: 2px solid #C5A880; padding: 10px 18px;" 
        data-bs-toggle="modal" data-bs-target="#welcomeWheelModal" title="عجلة الحظ والخصومات">
    <i class="bi bi-gift-fill text-warning fs-5"></i>
    <span class="small fw-bold">عجلة الحظ</span>
</button>
<?php endif; ?>

<!-- 8. INSTAGRAM / SOCIAL SECTION -->
<section class="py-5">
    <div class="container text-center">
        <span class="section-tag">@MANON_FASHION</span>
        <h2 class="section-title mb-4">شاركينا إطلالتك عبر إنستغرام</h2>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="rounded-3 overflow-hidden shadow-sm">
                    <img src="assets/images/abaya1.jpg" alt="Instagram 1" class="w-100" style="height: 240px; object-fit: cover;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="rounded-3 overflow-hidden shadow-sm">
                    <img src="assets/images/abaya2.jpg" alt="Instagram 2" class="w-100" style="height: 240px; object-fit: cover;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="rounded-3 overflow-hidden shadow-sm">
                    <img src="assets/images/abaya3.jpg" alt="Instagram 3" class="w-100" style="height: 240px; object-fit: cover;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="rounded-3 overflow-hidden shadow-sm">
                    <img src="assets/images/abaya6.jpg" alt="Instagram 4" class="w-100" style="height: 240px; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
