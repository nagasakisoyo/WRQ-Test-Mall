<?php
/**
 * VULN-007: Hidden unauthenticated API
 * 
 * No session/cookie/token check whatsoever.
 * Discoverable only by reverse-engineering the JS in assets/js/utils.js
 * where the URL is hidden via Base64-encoded string segments.
 */
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($uid <= 0) {
    echo json_encode(['error' => 'invalid parameter']);
    exit;
}

$pdo = get_pdo();
$stmt = $pdo->prepare("SELECT id, username, nickname, realname, gender, birthday, phone, email, address, avatar_src, create_time FROM user WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['code' => 0, 'data' => $user], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['code' => 404, 'msg' => 'user not found']);
}
