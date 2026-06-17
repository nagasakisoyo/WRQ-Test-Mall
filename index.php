<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'home':
        include __DIR__ . '/pages/home.php';
        break;
    case 'login':
        include __DIR__ . '/pages/login.php';
        break;
    case 'do_login':
        include __DIR__ . '/pages/do_login.php';
        break;
    case 'register':
        include __DIR__ . '/pages/register.php';
        break;
    case 'do_register':
        include __DIR__ . '/pages/do_register.php';
        break;
    case 'logout':
        user_logout();
        redirect(SITE_URL . '/index.php');
        break;
    case 'products':
        include __DIR__ . '/pages/product_list.php';
        break;
    case 'product':
        include __DIR__ . '/pages/product_detail.php';
        break;
    case 'cart':
        require_user_login();
        include __DIR__ . '/pages/cart.php';
        break;
    case 'order_confirm':
        require_user_login();
        include __DIR__ . '/pages/order_create.php';
        break;
    case 'order_pay':
        require_user_login();
        include __DIR__ . '/pages/order_pay.php';
        break;
    case 'orders':
        require_user_login();
        include __DIR__ . '/pages/order_list.php';
        break;
    case 'user_center':
        require_user_login();
        include __DIR__ . '/pages/user_center.php';
        break;
    case 'forgot':
        include __DIR__ . '/pages/forgot_password.php';
        break;
    default:
        include __DIR__ . '/pages/home.php';
        break;
}
