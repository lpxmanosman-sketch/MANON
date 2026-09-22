<?php
/**
 * Wheel of Fortune API - MANON
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'spin') {
    // Check if wheel is enabled in settings
    $wheelEnabled = getSetting('wheel_enabled', '1');
    if ($wheelEnabled != '1') {
        echo json_encode(['success' => false, 'message' => 'عجلة الحظ غير مفعلة حالياً']);
        exit;
    }

    // Check if visitor has already spun in this session
    if (!empty($_SESSION['wheel_spun'])) {
        echo json_encode(['success' => false, 'message' => 'لقد قمتِ بتجربة عجلة الحظ مسبقاً']);
        exit;
    }

    // Available discount percentages
    $discounts = [5, 10, 1];
    $selectedPercent = $discounts[array_rand($discounts)];

    // Generate unique code: MANON{percent}-{random 4 chars}
    $randomPart = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    $code = "MANON{$selectedPercent}-{$randomPart}";

    // Set expiration: 7 days
    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO discount_codes (code, discount_percent, expires_at, is_used) VALUES (?, ?, ?, 0)");
        $stmt->execute([$code, $selectedPercent, $expiresAt]);

        $_SESSION['wheel_spun'] = true;
        $_SESSION['wheel_code'] = $code;
        $_SESSION['wheel_percent'] = $selectedPercent;

        echo json_encode([
            'success'   => true,
            'percent'   => $selectedPercent,
            'code'      => $code,
            'expiresAt' => date('Y/m/d', strtotime($expiresAt)),
            'message'   => "مبروك! حصلتِ على خصم {$selectedPercent}% 🎉"
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ غير متوقع']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
