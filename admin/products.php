<?php
/**
 * Admin - Products Management
 */
$adminTitle = 'إدارة العبايات والمنتجات';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$delId]);
        setFlash('success', 'تم حذف العباية بنجاح.');
    } catch (Exception $e) {
        setFlash('danger', 'لا يمكن حذف هذا المنتج لوجود طلبات مرتبطة به في النظام.');
    }
    header('Location: products.php');
    exit;
}

// Filters
$catFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE 1=1
";
$params = [];

if ($catFilter > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $catFilter;
}

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY p.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">العبايات والمنتجات</h4>
        <p class="text-muted small mb-0">إجمالي المعروض: <?= count($products) ?> عباية</p>
    </div>
    <div>
        <a href="product-add.php" class="btn btn-dark">
            <i class="bi bi-plus-lg me-1"></i> إضافة عباية جديدة
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="bg-white p-3 rounded-4 shadow-sm border border-secondary-subtle mb-4">
    <form action="products.php" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الكود..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="0">جميع التصنيفات</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($catFilter == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm w-100">تصفية</button>
            <?php if (!empty($search) || $catFilter > 0): ?>
                <a href="products.php" class="btn btn-outline-secondary btn-sm">إلغاء</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="bg-white rounded-4 shadow-sm border border-secondary-subtle overflow-hidden">
    <?php if (empty($products)): ?>
        <div class="p-5 text-center text-muted">
            <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
            لا توجد منتجات مطابقة لخيارات البحث.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th style="width: 70px;">الصورة</th>
                        <th>اسم العباية / الكود</th>
                        <th>التصنيف</th>
                        <th>السعر</th>
                        <th>المخزون</th>
                        <th>الشارات</th>
                        <th>الحالة</th>
                        <th class="text-center" style="width: 130px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <img src="<?= BASE_URL . e($p['main_image']) ?>" alt="" class="rounded-3 border object-fit-cover" style="width: 55px; height: 70px;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($p['name']) ?></div>
                                <div class="small text-muted">كود: <?= e($p['sku'] ?: '—') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($p['category_name']) ?></span>
                            </td>
                            <td>
                                <?php if ($p['sale_price']): ?>
                                    <div class="fw-bold text-danger"><?= formatPrice($p['sale_price']) ?></div>
                                    <div class="small text-muted text-decoration-line-through"><?= formatPrice($p['price']) ?></div>
                                <?php else: ?>
                                    <div class="fw-bold text-dark"><?= formatPrice($p['price']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['stock_quantity'] > 5): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $p['stock_quantity'] ?> متوفر</span>
                                <?php elseif ($p['stock_quantity'] > 0): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><?= $p['stock_quantity'] ?> متبقي قليل</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">نفد المخزون</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['is_featured']): ?><span class="badge bg-warning text-dark me-1">مميز</span><?php endif; ?>
                                <?php if ($p['is_new']): ?><span class="badge bg-danger">جديد</span><?php endif; ?>
                            </td>
                            <td>
                                <?= $p['is_active'] ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">معطل</span>' ?>
                            </td>
                            <td class="text-center">
                                <a href="product-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= BASE_URL ?>product.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="معاينة في المتجر">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <a href="products.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنتِ متأكدة من حذف هذا المنتج نهائياً؟')" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
