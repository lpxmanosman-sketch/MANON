<?php
/**
 * Admin - Categories Management
 */
$adminTitle = 'إدارة تصنيفات العبايات';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (!empty($name)) {
            $slug = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($name));
            $slug = trim($slug, '-') ?: 'cat-' . time();

            // Image
            $imgPath = 'assets/images/cat1.jpg';
            if (!empty($_FILES['image']['name'])) {
                $upload = handleImageUpload($_FILES['image'], 'categories');
                if ($upload['success']) {
                    $imgPath = $upload['path'];
                }
            }

            try {
                $stmt = $db->prepare("INSERT INTO categories (name, slug, description, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $imgPath, $sortOrder, $isActive]);
                setFlash('success', 'تمت إضافة التصنيف بنجاح!');
            } catch (Exception $e) {
                setFlash('danger', 'حدث خطأ: قد يكون اسم التصنيف أو الرابط مكرراً.');
            }
        }
    }
    header('Location: categories.php');
    exit;
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    // Check if products exist in this category
    $prodCount = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $prodCount->execute([$delId]);
    if ($prodCount->fetchColumn() > 0) {
        setFlash('danger', 'لا يمكن حذف هذا التصنيف لوجود عبايات مرتبطة به. يرجى نقل العبايات أو حذفها أولاً.');
    } else {
        $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$delId]);
        setFlash('success', 'تم حذف التصنيف بنجاح.');
    }
    header('Location: categories.php');
    exit;
}

// Fetch categories with product counts
$sql = "
    SELECT c.*, COUNT(p.id) AS products_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC, c.id ASC
";
$categories = $db->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">تصنيفات ومجموعات العبايات</h4>
        <p class="text-muted small mb-0">إدارة الأقسام وتحديد ترتيب ظهورها في المتجر</p>
    </div>
    <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#addCategoryCollapse">
        <i class="bi bi-plus-lg me-1"></i> إضافة تصنيف جديد
    </button>
</div>

<!-- Add Category Collapse Form -->
<div class="collapse mb-4" id="addCategoryCollapse">
    <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
        <h5 class="fw-bold mb-3 pb-2 border-bottom">إضافة تصنيف جديد</h5>
        <form action="categories.php" method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">اسم التصنيف <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="مثال: عبايات كلوش فاخرة" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">ترتيب الظهور</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">صورة التصنيف</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">وصف التصنيف</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="وصف قصير يوضح طابع المجموعة وخاماتها..."></textarea>
                </div>
                <div class="col-12 d-flex justify-content-between align-items-center pt-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="newCatActive" checked>
                        <label class="form-check-label fw-bold small" for="newCatActive">تفعيل التصنيف وعرضه فوراً</label>
                    </div>
                    <button type="submit" class="btn btn-dark px-4">حفظ التصنيف</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="bg-white rounded-4 shadow-sm border border-secondary-subtle overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th style="width: 80px;">الصورة</th>
                    <th>اسم التصنيف</th>
                    <th>الرابط (Slug)</th>
                    <th>عدد العبايات</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th class="text-center" style="width: 120px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <img src="<?= BASE_URL . e($cat['image'] ?: 'assets/images/cat1.jpg') ?>" class="rounded-3 border object-fit-cover" style="width: 60px; height: 50px;">
                        </td>
                        <td>
                            <strong class="text-dark"><?= e($cat['name']) ?></strong>
                            <div class="small text-muted text-truncate" style="max-width: 300px;"><?= e($cat['description']) ?></div>
                        </td>
                        <td><code><?= e($cat['slug']) ?></code></td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2"><?= $cat['products_count'] ?> عباية</span>
                        </td>
                        <td><?= $cat['sort_order'] ?></td>
                        <td>
                            <?= $cat['is_active'] ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">معطل</span>' ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= BASE_URL ?>shop.php?category=<?= e($cat['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-info" title="معاينة بالمتجر">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف هذا التصنيف؟')" title="حذف">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
