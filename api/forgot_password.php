<?php
/**
 * VULN-008: Arbitrary password reset via response tampering
 * 
 * Step 2 (verify_answer) returns success/false in JSON.
 * Step 3 (reset_password) only checks $_SESSION['reset_username'],
 * which is set in Step 1 regardless of Step 2 outcome.
 * An attacker can intercept the Step 2 response, change success to true,
 * and proceed to Step 3 to reset anyone's password.
 */
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$step = $_POST['step'] ?? '';
$pdo = get_pdo();

if ($step === 'check_user') {
    $username = $_POST['username'] ?? '';
    $stmt = $pdo->prepare("SELECT id, security_question FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['reset_username'] = $username;
        echo json_encode(['success' => true, 'question' => $user['security_question']]);
    } else {
        echo json_encode(['success' => false, 'msg' => '用户不存在']);
    }
    exit;
}

if ($step === 'verify_answer') {
    $username = $_SESSION['reset_username'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $stmt = $pdo->prepare("SELECT security_answer FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $user['security_answer'] === $answer) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'msg' => '密保答案错误']);
    }
    // NOTE: No $_SESSION['answer_verified'] flag is set — this is the vulnerability
    exit;
}

if ($step === 'reset_password') {
    $username = $_SESSION['reset_username'] ?? '';
    if (empty($username)) {
        echo json_encode(['success' => false, 'msg' => '会话已过期']);
        exit;
    }
    $newPassword = md5($_POST['new_password'] ?? '');
    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE username = ?");
    $stmt->execute([$newPassword, $username]);
    unset($_SESSION['reset_username']);
    echo json_encode(['success' => true, 'msg' => '密码重置成功']);
    exit;
}

echo json_encode(['success' => false, 'msg' => '无效请求']);
