<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_current_username() {
    return $_SESSION['username'] ?? null;
}

function require_user_login() {
    if (!is_user_logged_in()) {
        header('Location: ' . SITE_URL . '/index.php?action=login');
        exit;
    }
}

function user_login($user) {
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nickname'] = $user['nickname'];
}

function user_logout() {
    unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['nickname']);
    session_destroy();
}
