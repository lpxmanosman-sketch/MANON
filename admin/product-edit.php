<?php
/**
 * Admin - Edit Product
 */
$adminTitle = 'تعديل بيانات العباية';
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDB();

$stmt = $db->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'المنتج غير موجود.');
    header('Location: products.php');
    exit;
}

// Delete gallery image action
if (isset($_GET['del_img'])) {
    $imgId = (int)$_GET['del_img'];
    $db->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?")->execute([$imgId, $id]);
    setFlash('info', 'تم حذف الصورة من المعرض.');
    header('Location: product-edit.php?id=' . $id);
    exit;
}

$categories = $db->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();
$galleryImages = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$galleryImages->execute([$id]);
$gallery = $galleryImages->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'خطأ أمني: رمز CSRF غير صالح.';
    }

    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $sku = trim($_POST['sku'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)($_POST['stock_quantity'] ?? 10);
    $sizes = trim($_POST['sizes'] ?? '52, 54, 56, 58, 60');
    $fabric = trim($_POST['fabric'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $shortDesc = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) $errors[] = 'اسم العباية مطلوب.';
    if ($categoryId <= 0) $errors[] = 'يرجى اختيار تصنيف صالح.';
    if ($price <= 0) $errors[] = 'يرجى إدخال سعر صحيح.';

    $mainImagePath = $product['main_image'];
    if (!empty($_FILES['main_image']['name'])) {
        $uploadResult = handleImageUpload($_FILES['main_image'], 'products');
        if (!$uploadResult['success']) {
            $errors[] = 'خطأ في الصورة الرئيسية: ' . $uploadResult['error'];
        } else {
            $mainImagePath = $uploadResult['path'];
        }
    }

    if (empty($errors)) {
        try {
            $updateStmt = $db->prepare("
                UPDATE products SET
                    category_id = ?, name = ?, sku = ?, short_desc = ?, description = ?,
                    price = ?, sale_price = ?, stock_quantity = ?, sizes = ?, fabric = ?, color = ?,
                    main_image = ?, is_featured = ?, is_new = ?, is_active = ?
                WHERE id = ?
            ");

            $updateStmt->execute([
                $categoryId, $name, $sku, $shortDesc, $description,
                $price, $salePrice, $stock, $sizes, $fabric, $color,
                $mainImagePath, $isFeatured, $isNew, $isActive,
                $id
            ]);

            // Handle Additional Gallery Images
            if (!empty($_FILES['gallery_images']['name'][0])) {
                $galleryFiles = $_FILES['gallery_images'];
                $totalFiles = count($galleryFiles['name']);

                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($galleryFiles['error'][$i] === UPLOAD_ERR_OK) {
                        $singleFile = [
                            'name'     => $galleryFiles['name'][$i],
                            'type'     => $galleryFiles['type'][$i],
                            'tmp_name' => $galleryFiles['tmp_name'][$i],
                            'error'    => $galleryFiles['error'][$i],
                            'size'     => $galleryFiles['size'][$i]
                        ];
                        $gRes = handleImageUpload($singleFile, 'products');
                        if ($gRes['success']) {
                            $db->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)")
                               ->execute([$id, $gRes['path'], $i + 1]);
                        }
                    }
                }
            }

            setFlash('success', 'تم تحديث بيانات العباية بنجاح!');
            header('Location: product-edit.php?id=' . $id);
            exit;

        } catch (Exception $e) {
            $errors[] = 'خطأ أثناء التحديث: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <a href="products.php" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-right me-1"></i> العودة للقائمة
        </a>
        <h4 class="fw-bold text-dark mb-0">تعديل العباية: <?= e($product['name']) ?></h4>
    </div>
    <a href="<?= BASE_URL ?>product.php?id=<?= $product['id'] ?>" target="_blank" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-eye me-1"></i> معاينة في المتجر
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger rounded-4 shadow-sm mb-4">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="product-edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle mb-4">
                <h5 class="fw-bold mb-3 pb-2 border-bottom">بيانات العباية</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">اسم العباية <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-lg" value="<?= e($product['name']) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">التصنيف <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">كود المنتج (SKU)</label>
                        <input type="text" name="sku" class="form-control" value="<?= e($product['sku']) ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">السعر الأصلي (ج.م) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" step="0.5" value="<?= e($product['price']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">سعر الخصم (ج.م)</label>
                        <input type="number" name="sale_price" class="form-control" step="0.5" value="<?= e($product['sale_price']) ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">الخامة والقماش</label>
                        <input type="text" name="fabric" class="form-control" value="<?= e($product['fabric']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">اللون</label>
                        <input type="text" name="color" class="form-control" value="<?= e($product['color']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">المقاسات</label>
                    <input type="text" name="sizes" class="form-control" value="<?= e($product['sizes']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">الوصف الموجز</label>
                    <textarea name="short_desc" class="form-control" rows="2"><?= e($product['short_desc']) ?></textarea>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold">الوصف التفصيلي</label>
                    <textarea name="description" class="form-control" rows="5"><?= e($product['description']) ?></textarea>
                </div>
            </div>

            <!-- Gallery Images Management -->
            <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
                <h5 class="fw-bold mb-3 pb-2 border-bottom">معرض صور العباية الحالي</h5>
                <?php if (empty($gallery)): ?>
                    <p class="text-muted small mb-3">لا توجد صور إضافية في المعرض.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        <?php foreach ($gallery as $g): ?>
                            <div class="position-relative border rounded-3 p-1" style="width: 100px; height: 120px;">
                                <img src="<?= BASE_URL . e($g['image_path']) ?>" class="w-100 h-100 object-fit-cover rounded-2">
                                <a href="product-edit.php?id=<?= $id ?>&del_img=<?= $g['id'] ?>" class="btn btn-danger btn-sm position-absolute top-0 start-0 p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; transform: translate(-30%, -30%);" onclick="return confirm('حذف هذه الصورة؟')">
                                    <i class="bi bi-x"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <label class="form-label small fw-bold">إضافة صور جديدة للمعرض</label>
                <input type="file" name="gallery_images[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Main Image -->
            <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle mb-4">
                <h5 class="fw-bold mb-3 pb-2 border-bottom">الصورة الرئيسية</h5>
                <div class="text-center mb-3">
                    <img src="<?= BASE_URL . e($product['main_image']) ?>" class="rounded-3 border object-fit-cover w-100" style="max-height: 280px;">
                </div>
                <label class="form-label small fw-bold">تغيير الصورة الرئيسية</label>
                <input type="file" name="main_image" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>

            <!-- Status & Stock -->
            <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle mb-4">
                <h5 class="fw-bold mb-3 pb-2 border-bottom">الحالة والمخزون</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">الكمية في المخزن</label>
                    <input type="number" name="stock_quantity" class="form-control" value="<?= e($product['stock_quantity']) ?>" min="0">
                </div>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= $product['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold small" for="isActive">تفعيل العرض في المتجر</label>
                </div>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" <?= $product['is_featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold small" for="isFeatured">تمييز في قسم المختارات</label>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_new" id="isNew" <?= $product['is_new'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold small" for="isNew">عرض في وصل حديثاً</label>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-2 fs-6">
                    <i class="bi bi-check2-circle me-1"></i> حفظ التعديلات
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
