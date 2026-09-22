<?php
/**
 * Admin Panel Header Layout
 */
require_once __DIR__ . '/auth.php';

$adminUser = $_SESSION['admin_user'];
$currentPage = basename($_SERVER['PHP_SELF']);

// Count new orders, special orders, and pending reviews for badges
$db = getDB();
$newOrdersCount = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn();
$newSpecialOrdersCount = $db->query("SELECT COUNT(*) FROM special_orders WHERE status = 'new'")->fetchColumn();
$pendingReviewsCount = $db->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? e($adminTitle) . ' | لوحة تحكم منون' : 'لوحة تحكم منون الفاخرة' ?></title>

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #141414;
            --sidebar-color: #B3ADA5;
            --gold-accent: #C5A880;
            --body-bg: #F8F6F2;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--body-bg);
            color: #1A1A1A;
            margin: 0;
            direction: rtl;
        }
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .admin-sidebar {
            width: 270px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-color);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #292420;
            transition: all 0.3s ease;
        }
        .sidebar-brand {
            padding: 24px 20px;
            text-align: center;
            border-bottom: 1px solid #24201C;
        }
        .sidebar-brand img {
            max-height: 55px;
            background: #fff;
            padding: 4px;
            border-radius: 8px;
        }
        .sidebar-nav {
            list-style: none;
            padding: 15px 10px;
            margin: 0;
            flex-grow: 1;
        }
        .sidebar-nav li {
            margin-bottom: 5px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            color: #A6A097;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }
        .sidebar-link i {
            font-size: 1.2rem;
        }
        .sidebar-link:hover,
        .sidebar-link.active {
            background-color: #221F1C;
            color: var(--gold-accent);
        }
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(197, 168, 128, 0.15) 0%, rgba(197, 168, 128, 0.05) 100%);
            border-right: 3px solid var(--gold-accent);
        }
        .admin-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        .admin-topbar {
            background: #FFFFFF;
            border-bottom: 1px solid #E5DFD7;
            padding: 14px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-content {
            padding: 30px;
            flex-grow: 1;
        }
        .stat-card {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #E8E2D8;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            transition: transform 0.25s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: #F5EFEB;
            color: var(--gold-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        @media (max-width: 991.98px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                right: -280px;
                z-index: 1050;
            }
            .admin-sidebar.show {
                right: 0;
            }
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <a href="dashboard.php">
                <img src="<?= BASE_URL ?>assets/images/logo.jpg" alt="MANON">
            </a>
            <div class="small text-muted mt-2 fw-bold">لوحة الإدارة والمبيعات</div>
        </div>

        <ul class="sidebar-nav">
            <li>
                <a href="dashboard.php" class="sidebar-link <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>الرئيسية والإحصائيات</span>
                </a>
            </li>
            <li>
                <a href="orders.php" class="sidebar-link <?= (in_array($currentPage, ['orders.php', 'order-details.php'])) ? 'active' : '' ?>">
                    <i class="bi bi-bag-check"></i>
                    <span>إدارة الطلبات</span>
                    <?php if ($newOrdersCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto"><?= $newOrdersCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="products.php" class="sidebar-link <?= (in_array($currentPage, ['products.php', 'product-add.php', 'product-edit.php'])) ? 'active' : '' ?>">
                    <i class="bi bi-tag"></i>
                    <span>العبايات والمنتجات</span>
                </a>
            </li>
            <li>
                <a href="categories.php" class="sidebar-link <?= ($currentPage == 'categories.php') ? 'active' : '' ?>">
                    <i class="bi bi-grid"></i>
                    <span>تصنيفات المتجر</span>
                </a>
            </li>
            <li>
                <a href="colors.php" class="sidebar-link <?= ($currentPage == 'colors.php') ? 'active' : '' ?>">
                    <i class="bi bi-palette"></i>
                    <span>إدارة الألوان المتاحة</span>
                </a>
            </li>
            <li>
                <a href="special-orders.php" class="sidebar-link <?= ($currentPage == 'special-orders.php') ? 'active' : '' ?>">
                    <i class="bi bi-scissors"></i>
                    <span>الطلبات الخاصة</span>
                    <?php if ($newSpecialOrdersCount > 0): ?>
                        <span class="badge bg-warning text-dark rounded-pill ms-auto"><?= $newSpecialOrdersCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="discount-codes.php" class="sidebar-link <?= ($currentPage == 'discount-codes.php') ? 'active' : '' ?>">
                    <i class="bi bi-gift"></i>
                    <span>عجلة الحظ وأكواد الخصم</span>
                </a>
            </li>
            <li>
                <a href="reviews.php" class="sidebar-link <?= ($currentPage == 'reviews.php') ? 'active' : '' ?>">
                    <i class="bi bi-chat-quote"></i>
                    <span>آراء وتقييمات العملاء</span>
                    <?php if ($pendingReviewsCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto"><?= $pendingReviewsCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="customers.php" class="sidebar-link <?= ($currentPage == 'customers.php') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>سجل العميلات</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="sidebar-link <?= ($currentPage == 'settings.php') ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>إعدادات المتجر</span>
                </a>
            </li>
            <li class="mt-4 border-top border-secondary-subtle pt-3">
                <a href="<?= BASE_URL ?>index.php" target="_blank" class="sidebar-link text-warning">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>زيارة المتجر للعملاء</span>
                </a>
            </li>
            <li>
                <a href="logout.php" class="sidebar-link text-danger">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>تسجيل الخروج</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-lg-none" type="button" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0 fw-bold text-dark"><?= isset($adminTitle) ? e($adminTitle) : 'لوحة التحكم' ?></h5>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-light border d-flex align-items-center gap-2 rounded-pill px-3 py-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5 text-secondary"></i>
                        <span class="fw-bold small"><?= e($adminUser['name']) ?></span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i> الإعدادات</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i> تسجيل الخروج</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Dynamic Body Content Starts Here -->
        <main class="admin-content">
            <?php 
            $flash = getFlash();
            if ($flash): 
            ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
