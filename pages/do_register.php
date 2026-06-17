<?php
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$nickname = trim($_POST['nickname'] ?? '');
$sq = trim($_POST['security_question'] ?? '');
$sa = trim($_POST['security_answer'] ?? '');

if ($username === '' || $password === '' || $nickname === '') {
    json_response(['success' => false, 'msg' => '请填写必填项']);
}

$pdo = get_pdo();
$stmt = $pdo->prepare("SELECT id FROM user WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'msg' => '用户名已存在']);
}

$stmt = $pdo->prepare("INSERT INTO user (username, nickname, password, security_question, security_answer) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$username, $nickname, md5($password), $sq, $sa]);

json_response(['success' => true]);
