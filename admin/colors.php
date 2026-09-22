<?php
/**
 * Admin - Manage Colors
 */
$adminTitle = 'إدارة الألوان المتاحة';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Handle Actions (Add, Edit, Delete, Toggle Active)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $hex = trim($_POST['hex_code'] ?? '#000000');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (!empty($name)) {
            $stmt = $db->prepare("INSERT INTO colors (name, hex_code, is_active, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $hex, $isActive, $sortOrder]);
            setFlash('success', 'تمت إضافة اللون بنجاح');
        } else {
            setFlash('danger', 'يرجى إدخال اسم اللون');
        }
        redirect('colors.php');
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $hex = trim($_POST['hex_code'] ?? '#000000');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($id && !empty($name)) {
            $stmt = $db->prepare("UPDATE colors SET name = ?, hex_code = ?, is_active = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $hex, $isActive, $sortOrder, $id]);
            setFlash('success', 'تم تحديث بيانات اللون بنجاح');
        }
        redirect('colors.php');
    }

    if ($action === 'toggle_active') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("UPDATE colors SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
            setFlash('success', 'تم تغيير حالة التفعيل');
        }
        redirect('colors.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM colors WHERE id = ?")->execute([$id]);
            setFlash('success', 'تم حذف اللون بنجاح');
        }
        redirect('colors.php');
    }
}

// Fetch all colors
$colors = $db->query("SELECT * FROM colors ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">لوحة الألوان والخيارات</h4>
        <p class="text-muted small mb-0">تحكمي في الألوان المتاحة لاختيار العميلات في صفحات المنتجات والطلبات الخاصة</p>
    </div>
    <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addColorModal">
        <i class="bi bi-plus-lg ms-1"></i> إضافة لون جديد
    </button>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom border-light-subtle">
                <h5 class="fw-bold mb-0 text-dark">الألوان الحالية (<?= count($colors) ?>)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>معاينة اللون</th>
                            <th>اسم اللون</th>
                            <th>كود اللون (Hex)</th>
                            <th>ترتيب العرض</th>
                            <th>الحالة</th>
                            <th class="text-end pe-4">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($colors)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد ألوان مسجلة حتى الآن</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($colors as $index => $color): ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span style="width: 28px; height: 28px; border-radius: 50%; background-color: <?= e($color['hex_code']) ?>; display: inline-block; border: 2px solid #ddd; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><?= e($color['name']) ?></span>
                                    </td>
                                    <td>
                                        <code><?= e($color['hex_code']) ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= $color['sort_order'] ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_active">
                                            <input type="hidden" name="id" value="<?= $color['id'] ?>">
                                            <?php if ($color['is_active']): ?>
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" title="انقر للتعطيل">
                                                    <i class="bi bi-check-circle me-1"></i> مفعل
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-secondary rounded-pill px-3" title="انقر للتفعيل">
                                                    <i class="bi bi-dash-circle me-1"></i> معطل
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editColorModal<?= $color['id'] ?>" 
                                                    title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا اللون؟');" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $color['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editColorModal<?= $color['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold">تعديل اللون: <?= e($color['name']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body p-4 text-end">
                                                            <input type="hidden" name="action" value="edit">
                                                            <input type="hidden" name="id" value="<?= $color['id'] ?>">

                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">اسم اللون بالعربية <span class="text-danger">*</span></label>
                                                                <input type="text" name="name" class="form-control" value="<?= e($color['name']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">رمز اللون (Hex Code) <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <input type="color" class="form-control form-control-color" value="<?= e($color['hex_code']) ?>" onchange="document.getElementById('editHexInput<?= $color['id'] ?>').value = this.value">
                                                                    <input type="text" id="editHexInput<?= $color['id'] ?>" name="hex_code" class="form-control" value="<?= e($color['hex_code']) ?>" required dir="ltr">
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">ترتيب الظهور</label>
                                                                <input type="number" name="sort_order" class="form-control" value="<?= $color['sort_order'] ?>">
                                                            </div>
                                                            <div class="form-check form-switch mt-3">
                                                                <input class="form-check-input" type="checkbox" name="is_active" id="activeEdit<?= $color['id'] ?>" <?= $color['is_active'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label small fw-bold" for="activeEdit<?= $color['id'] ?>">تفعيل وظهور اللون في المتجر</label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top p-3">
                                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                                                            <button type="submit" class="btn btn-dark rounded-pill px-4">حفظ التغييرات</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Color Modal -->
<div class="modal fade" id="addColorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">إضافة لون جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 text-end">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">اسم اللون بالعربية <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: أسود ملكي، كحلي، كراميل..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">رمز اللون (Hex Code) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" value="#111111" onchange="document.getElementById('addHexInput').value = this.value">
                            <input type="text" id="addHexInput" name="hex_code" class="form-control" value="#111111" required dir="ltr">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ترتيب الظهور</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="activeAdd" checked>
                        <label class="form-check-label small fw-bold" for="activeAdd">تفعيل وظهور اللون في المتجر</label>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4">إضافة اللون</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
