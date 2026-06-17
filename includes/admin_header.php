<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/admin_auth.php';
require_admin_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title ?? '后台管理') ?> - WRQTestMall</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url() ?>/assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url() ?>/admin/dashboard.php">WRQTestMall 后台</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/admin/dashboard.php">仪表盘</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/admin/products.php">商品管理</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/admin/orders.php">订单管理</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/admin/users.php">用户管理</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/admin/announcements.php">公告管理</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url() ?>/admin/profile.php">
                        <?= $_SESSION['admin_nickname'] ?? '管理员' ?>
                    </a>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/admin/logout.php">退出</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid mt-3">
<div class="row">
    <div class="col-md-12">
