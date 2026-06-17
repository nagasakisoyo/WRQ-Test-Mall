<?php
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$pdo = get_pdo();
$stmt = $pdo->prepare("SELECT * FROM user WHERE username = ? AND status = 0");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && $user['password'] === md5($password)) {
    user_login($user);
    json_response(['success' => true]);
} else {
    json_response(['success' => false, 'msg' => '用户名或密码错误']);
}
