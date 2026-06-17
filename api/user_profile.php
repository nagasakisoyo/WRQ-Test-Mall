<?php
/**
 * VULN-003: Horizontal privilege escalation (IDOR)
 * 
 * Only checks if *any* user is logged in, does NOT verify that
 * the requested uid matches the session user.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'msg' => '请先登录']);
    exit;
}

$uid = intval($_GET['uid'] ?? 0);
if ($uid <= 0) {
    echo json_encode(['success' => false, 'msg' => '参数错误']);
    exit;
}

$pdo = get_pdo();
$stmt = $pdo->prepare("SELECT id, username, nickname, realname, gender, birthday, address, phone, email, avatar_src, create_time FROM user WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['success' => true, 'data' => $user], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'msg' => '用户不存在']);
}
