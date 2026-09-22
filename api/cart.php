<?php
/**
 * Cart AJAX API
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
        $size = isset($_POST['size']) ? trim($_POST['size']) : '54';
        $color = isset($_POST['color']) ? trim($_POST['color']) : 'أسود';

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'معرّف المنتج غير صالح']);
            exit;
        }

        $result = addToCart($productId, $quantity, $size, $color);
        echo json_encode($result);
        exit;

    case 'apply_coupon':
        $coupon = trim($_POST['coupon_code'] ?? '');
        $subtotal = getCartSubtotal();
        $validation = validateDiscountCode($coupon, $subtotal);
        if ($validation['valid']) {
            $_SESSION['applied_coupon'] = [
                'code' => $validation['code'],
                'percent' => $validation['percent'],
                'discount_amount' => $validation['discount_amount']
            ];
            $totalAfter = max(0, $subtotal - $validation['discount_amount']);
            echo json_encode([
                'success' => true,
                'message' => $validation['message'],
                'percent' => $validation['percent'],
                'discountAmount' => $validation['discount_amount'],
                'discountFormatted' => formatPrice($validation['discount_amount']),
                'totalAfter' => $totalAfter,
                'totalAfterFormatted' => formatPrice($totalAfter)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $validation['message']
            ]);
        }
        exit;

    case 'remove_coupon':
        unset($_SESSION['applied_coupon']);
        $subtotal = getCartSubtotal();
        echo json_encode([
            'success' => true,
            'message' => 'تمت إزالة كود الخصم',
            'subtotal' => $subtotal,
            'subtotalFormatted' => formatPrice($subtotal)
        ]);
        exit;

    case 'update':
        $cartKey = $_POST['cart_key'] ?? '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        if (empty($cartKey)) {
            echo json_encode(['success' => false, 'message' => 'العنصر غير موجود']);
            exit;
        }

        updateCartItem($cartKey, $quantity);
        echo json_encode([
            'success'   => true,
            'cartCount' => getCartCount(),
            'subtotal'  => formatPrice(getCartSubtotal()),
            'subtotalRaw' => getCartSubtotal()
        ]);
        exit;

    case 'remove':
        $cartKey = $_POST['cart_key'] ?? '';
        if (!empty($cartKey)) {
            removeCartItem($cartKey);
        }
        echo json_encode([
            'success'   => true,
            'message'   => 'تم حذف المنتج من السلة',
            'cartCount' => getCartCount(),
            'subtotal'  => formatPrice(getCartSubtotal())
        ]);
        exit;

    case 'clear':
        clearCart();
        echo json_encode([
            'success'   => true,
            'cartCount' => 0,
            'subtotal'  => formatPrice(0)
        ]);
        exit;

    case 'get':
    default:
        echo json_encode([
            'success'   => true,
            'cartCount' => getCartCount(),
            'subtotal'  => formatPrice(getCartSubtotal()),
            'items'     => getCart()
        ]);
        exit;
}
