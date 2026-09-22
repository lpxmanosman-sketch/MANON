<?php
/**
 * Admin - Manage Special Orders (الطلبات الخاصة)
 */
$adminTitle = 'إدارة الطلبات الخاصة والتفصيل';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Handle Status Update or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'update_status' && $id) {
        $status = $_POST['status'] ?? 'new';
        $stmt = $db->prepare("UPDATE special_orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        setFlash('success', 'تم تحديث حالة الطلب الخاص بنجاح');
        redirect('special-orders.php');
    }

    if ($action === 'delete' && $id) {
        $stmt = $db->prepare("DELETE FROM special_orders WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'تم حذف الطلب الخاص');
        redirect('special-orders.php');
    }
}

// Filter by Status
$statusFilter = $_GET['status'] ?? 'all';
$whereClause = "";
$params = [];

if ($statusFilter !== 'all') {
    $whereClause = "WHERE status = ?";
    $params[] = $statusFilter;
}

$stmt = $db->prepare("SELECT * FROM special_orders $whereClause ORDER BY id DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Stats count
$statusCounts = [
    'all'         => $db->query("SELECT COUNT(*) FROM special_orders")->fetchColumn(),
    'new'         => $db->query("SELECT COUNT(*) FROM special_orders WHERE status = 'new'")->fetchColumn(),
    'contacted'   => $db->query("SELECT COUNT(*) FROM special_orders WHERE status = 'contacted'")->fetchColumn(),
    'in_progress' => $db->query("SELECT COUNT(*) FROM special_orders WHERE status = 'in_progress'")->fetchColumn(),
    'completed'   => $db->query("SELECT COUNT(*) FROM special_orders WHERE status = 'completed'")->fetchColumn(),
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">الطلبات الخاصة والتفصيل اليدوي</h4>
        <p class="text-muted small mb-0">متابعة طلبات التفصيل الخاصة واستفسارات المقاسات والألوان للعميلات</p>
    </div>
</div>

<!-- Status Filters Bar -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="special-orders.php?status=all" class="btn btn-sm <?= $statusFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill px-3">
        الكل (<?= $statusCounts['all'] ?>)
    </a>
    <a href="special-orders.php?status=new" class="btn btn-sm <?= $statusFilter === 'new' ? 'btn-warning text-dark' : 'btn-outline-secondary' ?> rounded-pill px-3">
        طلبات جديدة (<?= $statusCounts['new'] ?>)
    </a>
    <a href="special-orders.php?status=contacted" class="btn btn-sm <?= $statusFilter === 'contacted' ? 'btn-info text-white' : 'btn-outline-secondary' ?> rounded-pill px-3">
        تم التواصل (<?= $statusCounts['contacted'] ?>)
    </a>
    <a href="special-orders.php?status=in_progress" class="btn btn-sm <?= $statusFilter === 'in_progress' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
        قيد التنفيذ (<?= $statusCounts['in_progress'] ?>)
    </a>
    <a href="special-orders.php?status=completed" class="btn btn-sm <?= $statusFilter === 'completed' ? 'btn-success' : 'btn-outline-secondary' ?> rounded-pill px-3">
        مكتمل (<?= $statusCounts['completed'] ?>)
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">رقم الطلب</th>
                    <th>العميلة</th>
                    <th>رقم الهاتف</th>
                    <th>اللون والمقاس</th>
                    <th>وصف الموديل</th>
                    <th>صورة الموديل</th>
                    <th>تاريخ الطلب</th>
                    <th>الحالة</th>
                    <th class="text-end pe-4">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">لا توجد طلبات خاصة مطابقة لهذا الفلتر</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $so): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">#REQ-<?= str_pad($so['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <span class="fw-bold text-dark"><?= e($so['customer_name']) ?></span>
                            </td>
                            <td>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $so['customer_phone']) ?>" target="_blank" class="text-success fw-bold text-decoration-none">
                                    <i class="bi bi-whatsapp me-1"></i>
                                    <span dir="ltr"><?= e($so['customer_phone']) ?></span>
                                </a>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="badge bg-light text-dark border"><?= e($so['preferred_color'] ?: 'غير محدد') ?></span>
                                    <span class="badge bg-secondary"><?= e($so['size'] ?: 'قياسي') ?></span>
                                </div>
                            </td>
                            <td style="max-width: 250px;">
                                <div class="text-truncate small" title="<?= e($so['design_description']) ?>">
                                    <?= e($so['design_description']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($so['image_path'])): ?>
                                    <a href="<?= BASE_URL . e($so['image_path']) ?>" target="_blank">
                                        <img src="<?= BASE_URL . e($so['image_path']) ?>" alt="Model" class="rounded-3 shadow-sm" style="width: 48px; height: 48px; object-fit: cover; border: 1px solid #ddd;">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">لا يوجد صورة</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= date('Y/m/d H:i', strtotime($so['created_at'])) ?>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= $so['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 120px;">
                                        <option value="new" <?= $so['status'] === 'new' ? 'selected' : '' ?>>جديد</option>
                                        <option value="contacted" <?= $so['status'] === 'contacted' ? 'selected' : '' ?>>تم التواصل</option>
                                        <option value="in_progress" <?= $so['status'] === 'in_progress' ? 'selected' : '' ?>>قيد التنفيذ</option>
                                        <option value="completed" <?= $so['status'] === 'completed' ? 'selected' : '' ?>>مكتمل</option>
                                        <option value="cancelled" <?= $so['status'] === 'cancelled' ? 'selected' : '' ?>>ملغي</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-circle" data-bs-toggle="modal" data-bs-target="#viewSoModal<?= $so['id'] ?>" title="عرض التفاصيل الكاملة">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $so['customer_phone']) ?>?text=<?= urlencode("مرحباً بكِ أستاذة {$so['customer_name']} 🤍\nبخصوص طلبكِ الخاص في متجر منون رقم #REQ-{$so['id']}:") ?>" target="_blank" class="btn btn-sm btn-success rounded-circle" title="مراسلة العميل عبر واتساب">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                    <form method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب الخاص؟');" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $so['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- View Modal -->
                                <div class="modal fade" id="viewSoModal<?= $so['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title fw-bold">تفاصيل الطلب الخاص #REQ-<?= str_pad($so['id'], 4, '0', STR_PAD_LEFT) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4 text-end">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <div class="small text-muted">اسم العميلة</div>
                                                        <div class="fw-bold fs-6"><?= e($so['customer_name']) ?></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="small text-muted">رقم الهاتف</div>
                                                        <div class="fw-bold fs-6" dir="ltr"><?= e($so['customer_phone']) ?></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="small text-muted">اللون المفضل</div>
                                                        <div class="fw-bold"><?= e($so['preferred_color'] ?: 'غير محدد') ?></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="small text-muted">المقاس المطلوب</div>
                                                        <div class="fw-bold"><?= e($so['size'] ?: 'قياسي') ?></div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="small text-muted mb-1">وصف الموديل والتعديلات</div>
                                                    <div class="p-3 bg-light rounded-3 text-dark fs-6" style="white-space: pre-wrap;"><?= e($so['design_description']) ?></div>
                                                </div>

                                                <?php if (!empty($so['notes'])): ?>
                                                    <div class="mb-3">
                                                        <div class="small text-muted mb-1">ملاحظات إضافية</div>
                                                        <div class="p-3 bg-light rounded-3 text-dark small"><?= e($so['notes']) ?></div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($so['image_path'])): ?>
                                                    <div class="mb-3">
                                                        <div class="small text-muted mb-2">صورة الموديل المرفقة</div>
                                                        <img src="<?= BASE_URL . e($so['image_path']) ?>" alt="Design Model" class="img-fluid rounded-4 shadow-sm w-100" style="max-height: 350px; object-fit: contain; background: #fafafa;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer border-top p-3">
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $so['customer_phone']) ?>" target="_blank" class="btn btn-success rounded-pill px-4">
                                                    <i class="bi bi-whatsapp ms-1"></i> فتح محادثة واتساب
                                                </a>
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
                                            </div>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
