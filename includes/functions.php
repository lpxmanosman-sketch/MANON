<?php
/**
 * Core Helper Functions for MANON
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

/**
 * Sanitize Output (Prevent XSS)
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get Setting Value
 */
function getSetting($key, $default = '') {
    static $settingsCache = null;
    if ($settingsCache === null) {
        $db = getDB();
        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $settingsCache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $settingsCache = [];
        }
    }
    return $settingsCache[$key] ?? $default;
}

/**
 * Format Price
 */
function formatPrice($amount) {
    $currency = getSetting('currency', DEFAULT_CURRENCY);
    return number_format((float)$amount, 0) . ' ' . $currency;
}

/**
 * CSRF Protection
 */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Flash Messages
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Authentication Helpers
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_user']) && !empty($_SESSION['admin_user']['id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

/**
 * Cart Management (Session Based)
 */
function &getCart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function getCartCount() {
    $cart = getCart();
    $count = 0;
    foreach ($cart as $item) {
        $count += (int)($item['quantity'] ?? 0);
    }
    return $count;
}

function getCartSubtotal() {
    $cart = getCart();
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += ((float)$item['price'] * (int)$item['quantity']);
    }
    return $subtotal;
}

/**
 * Fetch active colors from database
 */
function getActiveColors() {
    $db = getDB();
    try {
        return $db->query("SELECT * FROM colors WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Exception $e) {
        return [
            ['name' => 'أسود', 'hex_code' => '#111111'],
            ['name' => 'بيج', 'hex_code' => '#D8CBB9'],
            ['name' => 'بني', 'hex_code' => '#5C4033'],
            ['name' => 'رمادي', 'hex_code' => '#808080'],
            ['name' => 'كحلي', 'hex_code' => '#0B1B3D'],
            ['name' => 'أبيض', 'hex_code' => '#FFFFFF']
        ];
    }
}

function addToCart($productId, $quantity = 1, $size = '54', $color = 'أسود') {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, price, sale_price, main_image, stock_quantity FROM products WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return ['success' => false, 'message' => 'المنتج غير متوفر حالياً'];
    }

    $color = !empty($color) ? trim($color) : 'أسود';
    $size = !empty($size) ? trim($size) : '54';
    $price = ($product['sale_price'] !== null && $product['sale_price'] > 0) ? (float)$product['sale_price'] : (float)$product['price'];
    $cartKey = $productId . '_' . $size . '_' . $color;

    $cart = &getCart();

    if (isset($cart[$cartKey])) {
        $newQty = $cart[$cartKey]['quantity'] + $quantity;
        if ($newQty > $product['stock_quantity']) {
            return ['success' => false, 'message' => 'الكمية المطلوبة تتجاوز المخزون المتاح'];
        }
        $cart[$cartKey]['quantity'] = $newQty;
    } else {
        if ($quantity > $product['stock_quantity']) {
            return ['success' => false, 'message' => 'الكمية المطلوبة تتجاوز المخزون المتاح'];
        }
        $cart[$cartKey] = [
            'product_id' => $product['id'],
            'name'       => $product['name'],
            'price'      => $price,
            'image'      => $product['main_image'],
            'size'       => $size,
            'color'      => $color,
            'quantity'   => (int)$quantity
        ];
    }

    return [
        'success'   => true,
        'message'   => 'تمت إضافة العباية إلى سلتك بنجاح',
        'cartCount' => getCartCount(),
        'subtotal'  => formatPrice(getCartSubtotal())
    ];
}

function updateCartItem($cartKey, $quantity) {
    $cart = &getCart();
    if (isset($cart[$cartKey])) {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            unset($cart[$cartKey]);
        } else {
            $cart[$cartKey]['quantity'] = $quantity;
        }
        return true;
    }
    return false;
}

function removeCartItem($cartKey) {
    $cart = &getCart();
    if (isset($cart[$cartKey])) {
        unset($cart[$cartKey]);
        return true;
    }
    return false;
}

function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Validate and compute discount code
 */
function validateDiscountCode($code, $subtotal) {
    $code = trim($code);
    if (empty($code)) {
        return ['valid' => false, 'message' => 'يرجى إدخال كود الخصم'];
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM discount_codes WHERE code = ? LIMIT 1");
    $stmt->execute([$code]);
    $disc = $stmt->fetch();

    if (!$disc) {
        return ['valid' => false, 'message' => 'كود الخصم غير صحيح'];
    }

    if ($disc['is_used'] == 1) {
        return ['valid' => false, 'message' => 'تم استخدام كود الخصم هذا من قبل'];
    }

    if (strtotime($disc['expires_at']) < time()) {
        return ['valid' => false, 'message' => 'انتهت صلاحية كود الخصم'];
    }

    $percent = (int)$disc['discount_percent'];
    $discountAmount = round(($subtotal * $percent) / 100, 2);

    return [
        'valid'           => true,
        'code'            => $disc['code'],
        'percent'         => $percent,
        'discount_amount' => $discountAmount,
        'message'         => "تم تطبيق خصم {$percent}% بنجاح!"
    ];
}

/**
 * Generate WhatsApp Order Message & URL
 * Updated with order number, color, discount, payment method
 */
function buildWhatsAppUrl($orderData, $items, $total, $discountAmount = 0, $paymentMethod = 'cod') {
    $orderNum = $orderData['order_number'] ?? ('#MANON-' . rand(1000, 9999));
    $paymentMethodText = ($paymentMethod === 'electronic') ? 'دفع إلكتروني' : 'الدفع عند الاستلام';

    $msg = "MANON ORDER\n\n";
    $msg .= "رقم الطلب: {$orderNum}\n";
    $msg .= "اسم العميل: " . $orderData['customer_name'] . "\n";
    $msg .= "رقم الهاتف: " . $orderData['phone'] . "\n";
    $msg .= "المحافظة: " . $orderData['city'] . "\n";
    $msg .= "العنوان: " . $orderData['address'] . "\n\n";

    $msg .= "المنتجات:\n\n";
    foreach ($items as $item) {
        $color = !empty($item['selected_color']) ? $item['selected_color'] : (!empty($item['color']) ? $item['color'] : 'أسود');
        $size = !empty($item['selected_size']) ? $item['selected_size'] : (!empty($item['size']) ? $item['size'] : '54');
        $itemTotal = $item['price'] * $item['quantity'];

        $msg .= "- " . $item['product_name'] . " (مقاس {$size})\n";
        $msg .= "اللون: " . $color . "\n";
        $msg .= "الكمية: " . $item['quantity'] . "\n";
        $msg .= "السعر: " . number_format($itemTotal, 0) . " " . DEFAULT_CURRENCY . "\n\n";
    }

    if ($discountAmount > 0) {
        $msg .= "الخصم: " . number_format($discountAmount, 0) . " " . DEFAULT_CURRENCY . "\n";
    }
    $msg .= "الإجمالي: " . number_format($total, 0) . " " . DEFAULT_CURRENCY . "\n\n";
    $msg .= "طريقة الدفع: " . $paymentMethodText . "\n";

    if (!empty($orderData['notes'])) {
        $msg .= "\nملاحظات: " . $orderData['notes'] . "\n";
    }

    $encoded = rawurlencode($msg);
    $phone = WHATSAPP_PHONE; // 201273572887
    return "https://wa.me/{$phone}?text={$encoded}";
}

/**
 * Generate Structured WhatsApp Message for Special / Custom Orders
 */
function buildSpecialOrderWhatsAppUrl($data) {
    $msg = "طلب خاص - MANON\n\n";
    $msg .= "الاسم: " . ($data['name'] ?? '') . "\n";
    $msg .= "رقم الهاتف: " . ($data['phone'] ?? '') . "\n";
    $msg .= "التصميم المطلوب: " . ($data['design'] ?? '') . "\n";
    $msg .= "اللون: " . ($data['color'] ?? '') . "\n";
    $msg .= "المقاس: " . ($data['size'] ?? '') . "\n";
    $msg .= "الملاحظات: " . ($data['notes'] ?? '') . "\n";

    $encoded = rawurlencode($msg);
    $phone = WHATSAPP_PHONE;
    return "https://wa.me/{$phone}?text={$encoded}";
}

/**
 * Secure Image Upload Helper
 */
function handleImageUpload($file, $subFolder = 'products') {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'معاملات الملف غير صالحة'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'error' => 'لم يتم تحديد أي ملف للرفع'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'error' => 'حجم الملف يتجاوز الحد المسموح'];
        default:
            return ['success' => false, 'error' => 'حدث خطأ غير متوقع أثناء الرفع'];
    }

    // Max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت'];
    }

    // MIME type check
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp'
    ];

    $ext = array_search($mime, $allowedMimes, true);
    if ($ext === false) {
        return ['success' => false, 'error' => 'نوع الصورة غير مدعوم. يرجى رفع ملف JPG أو PNG أو WebP'];
    }

    // Upload folder
    $uploadDir = ROOT_PATH . 'uploads/' . $subFolder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Safe random name
    $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'تعذر حفظ الملف في مجلد التخزين'];
    }

    return [
        'success' => true,
        'path' => 'uploads/' . $subFolder . '/' . $newFileName
    ];
}

/**
 * Status Badge for Orders
 */
function getStatusBadge($status) {
    switch ($status) {
        case 'new':
            return '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill">جديد (New)</span>';
        case 'confirmed':
            return '<span class="badge bg-info text-dark px-3 py-2 rounded-pill">مؤكد (Confirmed)</span>';
        case 'preparing':
            return '<span class="badge bg-primary px-3 py-2 rounded-pill">قيد التجهيز (Preparing)</span>';
        case 'delivered':
            return '<span class="badge bg-success px-3 py-2 rounded-pill">تم التوصيل (Delivered)</span>';
        case 'cancelled':
            return '<span class="badge bg-danger px-3 py-2 rounded-pill">ملغي (Cancelled)</span>';
        default:
            return '<span class="badge bg-secondary px-3 py-2 rounded-pill">' . e($status) . '</span>';
    }
}
