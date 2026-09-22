<?php
/**
 * Admin Dashboard - MANON
 */
$adminTitle = 'لوحة التحكم والإحصائيات';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// 1. Metrics
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalSales = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();

// 2. Recent Orders
$recentOrders = $db->query("
    SELECT * FROM orders 
    ORDER BY id DESC 
    LIMIT 6
")->fetchAll();

// 3. Top Products by Views
$topProducts = $db->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.views_count DESC, p.id DESC 
    LIMIT 5
")->fetchAll();
?>

<!-- Stat Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Sales -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-bold mb-1">إجمالي المبيعات</div>
                <div class="fs-3 fw-bold text-dark"><?= formatPrice($totalSales) ?></div>
                <div class="text-success small mt-1"><i class="bi bi-graph-up-arrow me-1"></i> إجمالي الطلبات النشطة</div>
            </div>
            <div class="stat-icon" style="background: #EBF7EE; color: #198754;">
                <i class="bi bi-currency-pound"></i>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-bold mb-1">إجمالي الطلبات</div>
                <div class="fs-3 fw-bold text-dark"><?= number_format($totalOrders) ?></div>
                <div class="text-muted small mt-1">طلبات عبر المتجر والواتساب</div>
            </div>
            <div class="stat-icon" style="background: #FFF8EB; color: #D4AF37;">
                <i class="bi bi-bag-check"></i>
            </div>
        </div>
    </div>

    <!-- Total Products -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-bold mb-1">العبايات في المتجر</div>
                <div class="fs-3 fw-bold text-dark"><?= number_format($totalProducts) ?></div>
                <div class="text-muted small mt-1">منتجات معروضة للبيع</div>
            </div>
            <div class="stat-icon" style="background: #F0F4FF; color: #0D6EFD;">
                <i class="bi bi-tag"></i>
            </div>
        </div>
    </div>

    <!-- Total Categories -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-bold mb-1">مجموعات وتصنيفات</div>
                <div class="fs-3 fw-bold text-dark"><?= number_format($totalCategories) ?></div>
                <div class="text-muted small mt-1">تصنيفات نشطة</div>
            </div>
            <div class="stat-icon" style="background: #FDF2F8; color: #D946EF;">
                <i class="bi bi-collection"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Recent Orders Table -->
    <div class="col-lg-8">
        <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-warning"></i> أحدث الطلبات الواردة</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-dark">عرض كل الطلبات</a>
            </div>

            <?php if (empty($recentOrders)): ?>
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">لا توجد طلبات مسجلة حتى الآن.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>اسم العميلة</th>
                                <th>المحافظة</th>
                                <th>الإجمالي</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ord): ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark">#<?= e($ord['order_number']) ?></strong>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= e($ord['customer_name']) ?></div>
                                        <div class="small text-muted"><?= e($ord['phone']) ?></div>
                                    </td>
                                    <td><?= e($ord['city']) ?></td>
                                    <td class="fw-bold text-dark"><?= formatPrice($ord['total']) ?></td>
                                    <td><?= getStatusBadge($ord['status']) ?></td>
                                    <td class="small text-muted"><?= date('Y/m/d H:i', strtotime($ord['created_at'])) ?></td>
                                    <td>
                                        <a href="order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-light border" title="تفاصيل الطلب">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions & Top Products -->
    <div class="col-lg-4">
        <!-- Quick Action Card -->
        <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle mb-4">
            <h5 class="fw-bold mb-3 pb-2 border-bottom">إجراءات سريعة</h5>
            <div class="d-grid gap-2">
                <a href="product-add.php" class="btn btn-dark text-start py-2">
                    <i class="bi bi-plus-circle me-2 text-warning"></i> إضافة عباية جديدة
                </a>
                <a href="categories.php" class="btn btn-outline-dark text-start py-2">
                    <i class="bi bi-folder-plus me-2 text-primary"></i> إدارة تصنيفات العبايات
                </a>
                <a href="orders.php?status=new" class="btn btn-outline-warning text-dark text-start py-2">
                    <i class="bi bi-bell me-2 text-warning"></i> متابعة الطلبات الجديدة (<?= $newOrdersCount ?>)
                </a>
                <a href="settings.php" class="btn btn-outline-secondary text-start py-2">
                    <i class="bi bi-sliders me-2"></i> تعديل بيانات ورقم الواتساب
                </a>
            </div>
        </div>

        <!-- Top Viewed Products -->
        <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
            <h5 class="fw-bold mb-3 pb-2 border-bottom">العبايات الأكثر مشاهدة</h5>
            <?php foreach ($topProducts as $p): ?>
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <img src="<?= BASE_URL . e($p['main_image']) ?>" class="rounded-2 border object-fit-cover" style="width: 45px; height: 55px;">
                    <div class="flex-grow-1">
                        <h6 class="mb-0 small fw-bold text-dark"><?= e($p['name']) ?></h6>
                        <div class="small text-muted"><?= formatPrice($p['sale_price'] ?: $p['price']) ?></div>
                    </div>
                    <span class="badge bg-light text-dark border small"><i class="bi bi-eye me-1"></i> <?= $p['views_count'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
