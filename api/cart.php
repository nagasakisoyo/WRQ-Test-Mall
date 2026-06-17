<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'msg' => '请先登录']);
    exit;
}

$action = $_GET['action'] ?? '';
$userId = get_current_user_id();
$pdo = get_pdo();

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);
        $number = max(1, intval($_POST['number'] ?? 1));
        $product = get_product($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'msg' => '商品不存在']);
            exit;
        }
        // check if already in cart
        $stmt = $pdo->prepare("SELECT * FROM product_order_item WHERE product_id = ? AND user_id = ? AND order_id IS NULL");
        $stmt->execute([$productId, $userId]);
        $existing = $stmt->fetch();
        if ($existing) {
            $newNum = $existing['number'] + $number;
            $newPrice = $product['sale_price'] * $newNum;
            $upd = $pdo->prepare("UPDATE product_order_item SET number = ?, price = ? WHERE id = ?");
            $upd->execute([$newNum, $newPrice, $existing['id']]);
        } else {
            $price = $product['sale_price'] * $number;
            $ins = $pdo->prepare("INSERT INTO product_order_item (number, price, product_id, user_id) VALUES (?, ?, ?, ?)");
            $ins->execute([$number, $price, $productId, $userId]);
        }
        echo json_encode(['success' => true]);
        break;

    case 'update':
        $itemId = intval($_POST['item_id'] ?? 0);
        $number = max(1, intval($_POST['number'] ?? 1));
        $stmt = $pdo->prepare("SELECT oi.*, p.sale_price FROM product_order_item oi LEFT JOIN product p ON oi.product_id = p.id WHERE oi.id = ? AND oi.user_id = ? AND oi.order_id IS NULL");
        $stmt->execute([$itemId, $userId]);
        $item = $stmt->fetch();
        if ($item) {
            $newPrice = $item['sale_price'] * $number;
            $upd = $pdo->prepare("UPDATE product_order_item SET number = ?, price = ? WHERE id = ?");
            $upd->execute([$number, $newPrice, $item['id']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'msg' => '购物车项不存在']);
        }
        break;

    case 'delete':
        $itemId = intval($_POST['item_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM product_order_item WHERE id = ? AND user_id = ? AND order_id IS NULL");
        $stmt->execute([$itemId, $userId]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'msg' => '未知操作']);
}
