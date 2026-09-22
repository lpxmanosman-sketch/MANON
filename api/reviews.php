<?php
/**
 * Customer Reviews API - MANON
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صحيحة']);
    exit;
}

$name = trim($_POST['customer_name'] ?? '');
$rating = isset($_POST['rating']) ? max(1, min(5, (int)$_POST['rating'])) : 5;
$comment = trim($_POST['comment'] ?? '');

if (empty($name) || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'يرجى كتابة الاسم والتعليق']);
    exit;
}

$db = getDB();
try {
    $stmt = $db->prepare("INSERT INTO reviews (customer_name, rating, comment, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$name, $rating, $comment]);

    echo json_encode([
        'success' => true,
        'message' => 'شكراً لمشاركتكِ رأيكِ 🤍 سيظهر تقييمكِ بعد مراجعته واعتماده من الإدارة.'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حفظ التقييم']);
}
