<?php
/**
 * VULN-004: Payment logic flaw — 0-yuan purchase
 * 
 * The server trusts the total_price field submitted by the client.
 * It does NOT re-calculate the price from the database.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'msg' => '请先登录']);
    exit;
}

$userId      = get_current_user_id();
$address     = $_POST['address_detail'] ?? '';
$receiver    = $_POST['receiver'] ?? '';
$mobile      = $_POST['mobile'] ?? '';
$postCode    = $_POST['post_code'] ?? '';
$totalPrice  = $_POST['total_price'] ?? '0';  // directly from client!
$itemsJson   = $_POST['items'] ?? '[]';

$items = json_decode($itemsJson, true);
if (empty($items) || $receiver === '' || $address === '') {
    echo json_encode(['success' => false, 'msg' => '参数不完整']);
    exit;
}

$pdo = get_pdo();
$orderCode = generate_order_code($userId);

$stmt = $pdo->prepare("INSERT INTO product_order (order_code, address_detail, post_code, receiver, mobile, status, user_id, total_price, pay_date, create_time) VALUES (?, ?, ?, ?, ?, 0, ?, ?, NOW(), NOW())");
$stmt->execute([$orderCode, $address, $postCode, $receiver, $mobile, $userId, $totalPrice]);
$orderId = $pdo->lastInsertId();

foreach ($items as $item) {
    $productId = intval($item['product_id']);
    $number    = intval($item['number']);
    $price     = floatval($item['subtotal'] ?? $item['price'] ?? 0);

    if (isset($item['cart_item_id'])) {
        $upd = $pdo->prepare("UPDATE product_order_item SET order_id = ? WHERE id = ? AND user_id = ?");
        $upd->execute([$orderId, $item['cart_item_id'], $userId]);
    } else {
        $ins = $pdo->prepare("INSERT INTO product_order_item (number, price, product_id, order_id, user_id) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$number, $price, $productId, $orderId, $userId]);
    }
}

echo json_encode([
    'success' => true,
    'order_code' => $orderCode,
    'pay_url' => SITE_URL . '/index.php?action=order_pay&code=' . $orderCode
], JSON_UNESCAPED_UNICODE);
