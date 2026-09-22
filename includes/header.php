<?php
/**
 * Global Header - MANON Luxury Abayas
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$announcement = getSetting('announcement', 'شحن مجاني على كافة الطلبات هذا الأسبوع | تصميمات استثنائية لأناقة تدوم');
$whatsappNumber = getSetting('whatsapp_number', WHATSAPP_RAW);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . SITE_NAME : SITE_NAME . ' - ' . SITE_SLOGAN ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? e($pageDesc) : 'متجر منون للأزياء والعبايات الفاخرة - تصاميم استثنائية تعكس أناقتك الملكية بأجود الأقمشة.' ?>">
    
    <!-- Open Graph / Meta -->
    <meta property="og:title" content="<?= isset($pageTitle) ? e($pageTitle) . ' | ' . SITE_NAME : SITE_NAME ?>">
    <meta property="og:description" content="متجر منون للأزياء والعبايات الفاخرة">
    <meta property="og:image" content="<?= BASE_URL ?>assets/images/logo.jpg">
    <meta property="og:url" content="<?= BASE_URL ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>assets/images/logo.jpg">

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Luxury CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

    <!-- 1. Top Announcement Bar -->
    <div class="announcement-bar text-center">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-none d-md-block">
                <i class="bi bi-whatsapp me-1 text-success"></i> خدمة العملاء: <a href="https://wa.me/<?= WHATSAPP_PHONE ?>" target="_blank"><?= e($whatsappNumber) ?></a>
            </div>
            <div class="mx-auto fw-bold text-center">
                <span>✨ <?= e($announcement) ?></span>
            </div>
            <div class="d-none d-md-block">
                <a href="<?= BASE_URL ?>admin/login.php" class="text-light text-decoration-none small opacity-75 hover-opacity-100">
                    <i class="bi bi-shield-lock me-1"></i> لوحة الإدارة
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Sticky Navbar -->
    <nav class="navbar navbar-expand-lg navbar-manon">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand logo-wrapper" href="<?= BASE_URL ?>index.php">
                <img src="<?= BASE_URL ?>assets/images/logo.jpg" alt="MANON Logo" class="logo-img">
            </a>

            <!-- Mobile Cart & Toggler -->
            <div class="d-flex align-items-center d-lg-none gap-2">
                <a href="<?= BASE_URL ?>cart.php" class="nav-icon-btn">
                    <i class="bi bi-bag"></i>
                    <span class="cart-badge cart-count-val"><?= $cartCount ?></span>
                </a>
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#manonNavbarNav">
                    <i class="bi bi-list fs-2 text-dark"></i>
                </button>
            </div>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse justify-content-center" id="manonNavbarNav">
                <ul class="navbar-nav align-items-center gap-1 my-3 my-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-manon <?= ($currentPage == 'index.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-manon <?= ($currentPage == 'shop.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>shop.php">متجر العبايات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-manon <?= ($currentPage == 'categories.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>categories.php">التصنيفات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-manon" href="<?= BASE_URL ?>shop.php?filter=new">كولكشن جديد</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-manon <?= ($currentPage == 'about.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>about.php">عن منون</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-manon <?= ($currentPage == 'contact.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>contact.php">تواصل معنا</a>
                    </li>
                </ul>
            </div>

            <!-- Header Action Icons (Desktop) -->
            <div class="d-none d-lg-flex align-items-center gap-2">
                <!-- Search Button -->
                <button class="nav-icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#searchModal" title="بحث في المتجر">
                    <i class="bi bi-search"></i>
                </button>

                <!-- WhatsApp Quick Chat -->
                <a href="https://wa.me/<?= WHATSAPP_PHONE ?>?text=<?= rawurlencode('مرحباً متجر منون، أود الاستفسار عن كولكشن العبايات الفاخرة') ?>" target="_blank" class="nav-icon-btn" title="تواصل واتساب">
                    <i class="bi bi-whatsapp text-success"></i>
                </a>

                <!-- Cart Button -->
                <a href="<?= BASE_URL ?>cart.php" class="nav-icon-btn" title="سلة المشتريات">
                    <i class="bi bi-bag"></i>
                    <span class="cart-badge cart-count-val"><?= $cartCount ?></span>
                </a>
            </div>
        </div>
    </nav>

    <!-- 3. Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 p-4" style="background: #FAF7F2;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">ابحثي في تشكيلة منون</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= BASE_URL ?>shop.php" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" name="q" class="form-control form-control-lg border-secondary-subtle" placeholder="اسم العباية، القصة، الخامة..." required autofocus>
                            <button class="btn btn-dark px-4" type="submit"><i class="bi bi-search"></i> بحث</button>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap gap-2 align-items-center pt-2">
                        <span class="small text-muted">الأكثر بحثاً:</span>
                        <a href="<?= BASE_URL ?>shop.php?q=كلوش" class="badge bg-white text-dark border p-2 text-decoration-none">كلوش</a>
                        <a href="<?= BASE_URL ?>shop.php?q=تطريز" class="badge bg-white text-dark border p-2 text-decoration-none">تطريز يدوي</a>
                        <a href="<?= BASE_URL ?>shop.php?q=سهرة" class="badge bg-white text-dark border p-2 text-decoration-none">سهرة فاخرة</a>
                        <a href="<?= BASE_URL ?>shop.php?q=بليزر" class="badge bg-white text-dark border p-2 text-decoration-none">بليزر عملي</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
