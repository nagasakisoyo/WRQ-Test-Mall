<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title ?? 'WRQTestMall') ?> - WRQTestMall</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url() ?>/index.php">WRQTestMall</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/index.php">首页</a></li>
                <?php try { $cats = get_categories(); } catch (Exception $e) { $cats = []; } ?>
                <?php foreach ($cats as $cat): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url() ?>/index.php?action=products&cid=<?= $cat['id'] ?>"><?= h($cat['name']) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_user_logged_in()): ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/index.php?action=cart">🛒 购物车</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/index.php?action=orders">我的订单</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/index.php?action=user_center">个人中心</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/index.php?action=logout">退出</a></li>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/index.php?action=login">登录</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>/index.php?action=register">注册</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4">
