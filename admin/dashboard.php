<?php
$page_title = '仪表盘';
include __DIR__ . '/../includes/admin_header.php';

$pdo = get_pdo();
$productTotal = $pdo->query("SELECT COUNT(*) FROM product WHERE is_enabled = 0")->fetchColumn();
$userTotal = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
$orderTotal = $pdo->query("SELECT COUNT(*) FROM product_order")->fetchColumn();
$orderCompleted = $pdo->query("SELECT COUNT(*) FROM product_order WHERE status = 3")->fetchColumn();
$revenue = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM product_order WHERE status >= 1")->fetchColumn();
?>

<h3>管理仪表盘</h3>
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body text-center">
                <h2><?= $productTotal ?></h2>
                <p class="mb-0">在售商品</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body text-center">
                <h2><?= $userTotal ?></h2>
                <p class="mb-0">注册用户</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body text-center">
                <h2><?= $orderTotal ?></h2>
                <p class="mb-0">总订单数</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body text-center">
                <h2>¥<?= format_price($revenue) ?></h2>
                <p class="mb-0">总营收</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">最近订单</div>
    <table class="table mb-0">
        <thead><tr><th>订单号</th><th>用户</th><th>金额</th><th>状态</th><th>时间</th></tr></thead>
        <tbody>
        <?php
        $statusMap = [0 => '待付款', 1 => '待发货', 2 => '待收货', 3 => '已完成', 4 => '已关闭'];
        $orders = $pdo->query("SELECT o.*, u.username FROM product_order o LEFT JOIN user u ON o.user_id = u.id ORDER BY o.create_time DESC LIMIT 10")->fetchAll();
        foreach ($orders as $o):
        ?>
        <tr>
            <td><?= h($o['order_code']) ?></td>
            <td><?= h($o['username']) ?></td>
            <td class="text-danger">¥<?= format_price($o['total_price']) ?></td>
            <td><?= $statusMap[$o['status']] ?? '未知' ?></td>
            <td><?= $o['create_time'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
