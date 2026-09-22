<?php
/**
 * Special / Custom Order API - MANON
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صالحة']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? $phone);
$design = trim($_POST['design'] ?? '');
$color = trim($_POST['color'] ?? '');
$size = trim($_POST['size'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (empty($name) || empty($phone) || empty($design)) {
    echo json_encode(['success' => false, 'message' => 'يرجى كتابة الاسم ورقم الهاتف وتفاصيل التصميم المطلوب']);
    exit;
}

// Optional Image Upload
$imagePath = null;
if (!empty($_FILES['design_image']['name'])) {
    $upload = handleImageUpload($_FILES['design_image'], 'special_orders');
    if ($upload['success']) {
        $imagePath = $upload['path'];
    }
}

// Insert into DB
$db = getDB();
try {
    $stmt = $db->prepare("
        INSERT INTO special_orders (customer_name, phone, whatsapp, design_description, preferred_color, size, notes, image_path, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new')
    ");
    $stmt->execute([$name, $phone, $whatsapp, $design, $color, $size, $notes, $imagePath]);
    $orderId = $db->lastInsertId();

    // Build structured WhatsApp URL
    $waUrl = buildSpecialOrderWhatsAppUrl([
        'name'   => $name,
        'phone'  => $phone,
        'design' => $design,
        'color'  => $color,
        'size'   => $size,
        'notes'  => $notes
    ]);

    echo json_encode([
        'success'  => true,
        'message'  => 'تم تسجيل طلبكِ الخاص بنجاح! جاري التوجيه إلى واتساب...',
        'order_id' => $orderId,
        'wa_url'   => $waUrl
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حفظ الطلب']);
}
