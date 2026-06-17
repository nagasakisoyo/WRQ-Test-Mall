<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function get_current_admin_id() {
    return $_SESSION['admin_id'] ?? null;
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function admin_login($admin) {
    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_nickname'] = $admin['nickname'];
    $_SESSION['admin_avatar']   = $admin['avatar_src'];
}

function admin_logout() {
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_nickname'], $_SESSION['admin_avatar']);
    session_destroy();
}
