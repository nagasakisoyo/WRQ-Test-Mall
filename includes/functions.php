<?php
require_once __DIR__ . '/db.php';

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function json_response($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function get_categories() {
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT * FROM category ORDER BY id ASC");
    return $stmt->fetchAll();
}

function get_product($id) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM product p LEFT JOIN category c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_product_images($product_id, $type = null) {
    $pdo = get_pdo();
    if ($type !== null) {
        $stmt = $pdo->prepare("SELECT * FROM product_image WHERE product_id = ? AND type = ? ORDER BY id ASC");
        $stmt->execute([$product_id, $type]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM product_image WHERE product_id = ? ORDER BY type ASC, id ASC");
        $stmt->execute([$product_id]);
    }
    return $stmt->fetchAll();
}

function format_price($price) {
    return number_format((float)$price, 2, '.', '');
}

function generate_order_code($user_id) {
    return date('YmdHis') . '0' . $user_id . rand(100, 999);
}

function base_url() {
    return SITE_URL;
}
