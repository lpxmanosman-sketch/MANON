<?php
/**
 * Global Footer - MANON Luxury Abayas
 */
$whatsappNumber = getSetting('whatsapp_number', WHATSAPP_RAW);
$storePhone = getSetting('phone_number', STORE_PHONE);
$storeEmail = getSetting('email', STORE_EMAIL);
$storeAddress = getSetting('address', STORE_ADDRESS);
?>
    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?= WHATSAPP_PHONE ?>?text=<?= rawurlencode('مرحباً MANON، أريد الاستفسار عن تفاصيل العبايات الفاخرة') ?>" class="floating-whatsapp" target="_blank" title="محادثة فورية عبر واتساب">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="mobile-bottom-nav">
        <a href="<?= BASE_URL ?>index.php" class="mobile-nav-item <?= ($currentPage == 'index.php') ? 'active' : '' ?>">
            <i class="bi bi-house"></i>
            <span>الرئيسية</span>
        </a>
        <a href="<?= BASE_URL ?>shop.php" class="mobile-nav-item <?= ($currentPage == 'shop.php') ? 'active' : '' ?>">
            <i class="bi bi-grid"></i>
            <span>المتجر</span>
        </a>
        <a href="<?= BASE_URL ?>categories.php" class="mobile-nav-item <?= ($currentPage == 'categories.php') ? 'active' : '' ?>">
            <i class="bi bi-collection"></i>
            <span>التصنيفات</span>
        </a>
        <a href="<?= BASE_URL ?>cart.php" class="mobile-nav-item <?= ($currentPage == 'cart.php') ? 'active' : '' ?>">
            <i class="bi bi-bag"></i>
            <span class="cart-badge cart-count-val" style="top:-2px; right: 50%; margin-right: -15px;"><?= $cartCount ?></span>
            <span>السلة</span>
        </a>
        <a href="https://wa.me/<?= WHATSAPP_PHONE ?>" target="_blank" class="mobile-nav-item text-success">
            <i class="bi bi-whatsapp"></i>
            <span>واتساب</span>
        </a>
    </div>

    <!-- Main Luxury Footer -->
    <footer class="footer-manon">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <!-- Col 1: Brand & Logo -->
                <div class="col-lg-4 col-md-6">
                    <img src="<?= BASE_URL ?>assets/images/logo.jpg" alt="MANON" class="footer-logo rounded-2 p-1 bg-white">
                    <p class="footer-desc mt-2">
                        <strong>MANON | منون</strong> - بيت الأزياء الرائد المتخصص في تصميم العبايات الخليجية الراقية والحديثة، حيث تلتقي فخامة التفاصيل بأجود الأقمشة لتمنحك إطلالة ملكية ساحرة في كل إطلالة.
                    </p>
                    <div class="d-flex align-items-center mt-3">
                        <a href="https://instagram.com" target="_blank" class="footer-social-link" title="إنستغرام"><i class="bi bi-instagram"></i></a>
                        <a href="https://facebook.com" target="_blank" class="footer-social-link" title="فيسبوك"><i class="bi bi-facebook"></i></a>
                        <a href="https://tiktok.com" target="_blank" class="footer-social-link" title="تيك توك"><i class="bi bi-tiktok"></i></a>
                        <a href="https://wa.me/<?= WHATSAPP_PHONE ?>" target="_blank" class="footer-social-link" title="واتساب"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-lg-2 col-md-3 col-6">
                    <h5 class="footer-heading">روابط سريعة</h5>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>index.php"><i class="bi bi-chevron-left text-muted small"></i> الرئيسية</a></li>
                        <li><a href="<?= BASE_URL ?>shop.php"><i class="bi bi-chevron-left text-muted small"></i> جميع العبايات</a></li>
                        <li><a href="<?= BASE_URL ?>categories.php"><i class="bi bi-chevron-left text-muted small"></i> تشكيلة التصنيفات</a></li>
                        <li><a href="<?= BASE_URL ?>shop.php?filter=featured"><i class="bi bi-chevron-left text-muted small"></i> الأكثر طلباً</a></li>
                        <li><a href="<?= BASE_URL ?>about.php"><i class="bi bi-chevron-left text-muted small"></i> قصة منون</a></li>
                        <li><a href="<?= BASE_URL ?>contact.php"><i class="bi bi-chevron-left text-muted small"></i> تواصل معنا</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Care -->
                <div class="col-lg-2 col-md-3 col-6">
                    <h5 class="footer-heading">خدمة العملاء</h5>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>contact.php"><i class="bi bi-chevron-left text-muted small"></i> جدول المقاسات</a></li>
                        <li><a href="<?= BASE_URL ?>contact.php"><i class="bi bi-chevron-left text-muted small"></i> الشحن والتوصيل</a></li>
                        <li><a href="<?= BASE_URL ?>contact.php"><i class="bi bi-chevron-left text-muted small"></i> الاستبدال والاسترجاع</a></li>
                        <li><a href="<?= BASE_URL ?>contact.php"><i class="bi bi-chevron-left text-muted small"></i> الأسئلة الشائعة</a></li>
                        <li><a href="<?= BASE_URL ?>admin/login.php"><i class="bi bi-lock text-muted small"></i> دخول المشرف</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Order via WhatsApp -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading">الطلب والاستفسار</h5>
                    <p class="text-light opacity-75 small mb-3">نسعد باستقبال طلباتك واستفساراتك على مدار الساعة عبر الواتساب المباشر:</p>
                    <div class="mb-3">
                        <a href="https://wa.me/<?= WHATSAPP_PHONE ?>" target="_blank" class="btn btn-whatsapp-order w-100 text-center">
                            <i class="bi bi-whatsapp fs-5"></i> اطلبي الآن عبر واتساب
                        </a>
                    </div>
                    <div class="small text-muted mb-2">
                        <i class="bi bi-telephone me-2 text-warning"></i> <?= e($whatsappNumber) ?>
                    </div>
                    <div class="small text-muted mb-2">
                        <i class="bi bi-geo-alt me-2 text-warning"></i> <?= e($storeAddress) ?>
                    </div>
                    <div class="small text-muted">
                        <i class="bi bi-envelope me-2 text-warning"></i> <?= e($storeEmail) ?>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div>
                    جميع الحقوق محفوظة &copy; <?= date('Y') ?> لعلامة <strong>MANON | منون</strong> للأزياء والعبايات.
                </div>
                <div class="text-muted small">
                    تصميم فاخر • عبايتك فخامة تليق بك
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Storefront Main JS -->
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
