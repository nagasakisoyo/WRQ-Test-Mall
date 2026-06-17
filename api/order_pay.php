<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'msg' => '请先登录']);
    exit;
}

$orderCode = $_POST['order_code'] ?? '';
$userId = get_current_user_id();
$pdo = get_pdo();

$stmt = $pdo->prepare("SELECT * FROM product_order WHERE order_code = ? AND user_id = ?");
$stmt->execute([$orderCode, $userId]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'msg' => '订单不存在']);
    exit;
}

if ($order['status'] != 0) {
    echo json_encode(['success' => false, 'msg' => '订单状态异常']);
    exit;
}

$upd = $pdo->prepare("UPDATE product_order SET status = 1, pay_date = NOW() WHERE id = ?");
$upd->execute([$order['id']]);

echo json_encode(['success' => true, 'msg' => '支付成功']);
