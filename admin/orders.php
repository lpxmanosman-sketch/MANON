<?php
/**
 * Admin - Orders Management
 */
$adminTitle = 'إدارة الطلبات والمبيعات';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Quick status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $orderId = (int)$_POST['order_id'];
        $newStatus = $_POST['status'] ?? 'new';
        $allowed = ['new', 'confirmed', 'preparing', 'delivered', 'cancelled'];
        if (in_array($newStatus, $allowed)) {
            $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            setFlash('success', 'تم تحديث حالة الطلب بنجاح.');
        }
    }
    header('Location: orders.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $sql .= " AND (order_number LIKE ? OR customer_name LIKE ? OR phone LIKE ? OR city LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Counts for filter tabs
$counts = [
    'all'       => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'new'       => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn(),
    'confirmed' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'confirmed'")->fetchColumn(),
    'preparing' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'preparing'")->fetchColumn(),
    'delivered' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn(),
    'cancelled' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn(),
];
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">إدارة الطلبات</h4>
        <p class="text-muted small mb-0">متابعة طلبات المتجر وتحديث مراحل التجهيز والشحن</p>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="orders.php" class="btn btn-sm <?= empty($statusFilter) ? 'btn-dark' : 'btn-light border' ?>">
        الكل (<?= $counts['all'] ?>)
    </a>
    <a href="orders.php?status=new" class="btn btn-sm <?= ($statusFilter === 'new') ? 'btn-warning text-dark fw-bold' : 'btn-light border' ?>">
        جديد (<?= $counts['new'] ?>)
    </a>
    <a href="orders.php?status=confirmed" class="btn btn-sm <?= ($statusFilter === 'confirmed') ? 'btn-info text-dark fw-bold' : 'btn-light border' ?>">
        مؤكد (<?= $counts['confirmed'] ?>)
    </a>
    <a href="orders.php?status=preparing" class="btn btn-sm <?= ($statusFilter === 'preparing') ? 'btn-primary text-white fw-bold' : 'btn-light border' ?>">
        قيد التجهيز (<?= $counts['preparing'] ?>)
    </a>
    <a href="orders.php?status=delivered" class="btn btn-sm <?= ($statusFilter === 'delivered') ? 'btn-success text-white fw-bold' : 'btn-light border' ?>">
        تم التوصيل (<?= $counts['delivered'] ?>)
    </a>
    <a href="orders.php?status=cancelled" class="btn btn-sm <?= ($statusFilter === 'cancelled') ? 'btn-danger text-white fw-bold' : 'btn-light border' ?>">
        ملغي (<?= $counts['cancelled'] ?>)
    </a>
</div>

<!-- Search Bar -->
<div class="bg-white p-3 rounded-4 shadow-sm border border-secondary-subtle mb-4">
    <form action="orders.php" method="GET" class="row g-2">
        <?php if (!empty($statusFilter)): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
        <div class="col-md-9">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="بحث برقم الطلب، اسم العميلة، أو رقم الهاتف..." value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm w-100">بحث</button>
            <?php if (!empty($search)): ?>
                <a href="orders.php<?= !empty($statusFilter) ? '?status=' . urlencode($statusFilter) : '' ?>" class="btn btn-outline-secondary btn-sm">إلغاء</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-4 shadow-sm border border-secondary-subtle overflow-hidden">
    <?php if (empty($orders)): ?>
        <div class="p-5 text-center text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            لا توجد طلبات تطابق هذا التحديد.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>رقم الطلب</th>
                        <th>بيانات العميلة</th>
                        <th>المحافظة</th>
                        <th>إجمالي الطلب</th>
                        <th>الحالة</th>
                        <th>تاريخ الطلب</th>
                        <th>تغيير سريع للحالة</th>
                        <th class="text-center" style="width: 100px;">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td>
                                <a href="order-details.php?id=<?= $ord['id'] ?>" class="fw-bold text-dark text-decoration-none">
                                    #<?= e($ord['order_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($ord['customer_name']) ?></div>
                                <div class="small text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-telephone"></i> <?= e($ord['phone']) ?>
                                    <a href="https://wa.me/20<?= preg_replace('/\D/', '', ltrim($ord['phone'], '0')) ?>" target="_blank" class="text-success ms-1" title="محادثة واتساب">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            </td>
                            <td><?= e($ord['city']) ?></td>
                            <td>
                                <span class="fw-bold text-dark fs-6"><?= formatPrice($ord['total']) ?></span>
                            </td>
                            <td><?= getStatusBadge($ord['status']) ?></td>
                            <td class="small text-muted"><?= date('Y/m/d H:i', strtotime($ord['created_at'])) ?></td>
                            <td>
                                <form action="orders.php" method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 130px;">
                                        <option value="new" <?= ($ord['status'] === 'new') ? 'selected' : '' ?>>جديد</option>
                                        <option value="confirmed" <?= ($ord['status'] === 'confirmed') ? 'selected' : '' ?>>مؤكد</option>
                                        <option value="preparing" <?= ($ord['status'] === 'preparing') ? 'selected' : '' ?>>قيد التجهيز</option>
                                        <option value="delivered" <?= ($ord['status'] === 'delivered') ? 'selected' : '' ?>>تم التوصيل</option>
                                        <option value="cancelled" <?= ($ord['status'] === 'cancelled') ? 'selected' : '' ?>>ملغي</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-center">
                                <a href="order-details.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-dark" title="عرض كافة التفاصيل">
                                    <i class="bi bi-eye"></i> تفاصيل
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
