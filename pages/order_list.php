<?php
$page_title = '我的订单';
$pdo = get_pdo();
$userId = get_current_user_id();
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM product_order WHERE user_id = ?";
$params = [$userId];
if ($status !== '' && is_numeric($status)) {
    $sql .= " AND status = ?";
    $params[] = intval($status);
}
$sql .= " ORDER BY create_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
$statusMap = [0 => '待付款', 1 => '待发货', 2 => '待收货', 3 => '已完成', 4 => '已关闭'];
$badgeMap = [0 => 'warning', 1 => 'info', 2 => 'primary', 3 => 'success', 4 => 'secondary'];
?>

<h3 style="font-family:var(--font-display);margin-bottom:1.5rem;">我的订单</h3>
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?= $status === '' ? 'active' : '' ?>" href="<?= base_url() ?>/index.php?action=orders">全部</a></li>
    <?php foreach ($statusMap as $k => $v): ?>
    <li class="nav-item"><a class="nav-link <?= $status !== '' && intval($status) === $k ? 'active' : '' ?>" href="<?= base_url() ?>/index.php?action=orders&status=<?= $k ?>"><?= $v ?></a></li>
    <?php endforeach; ?>
</ul>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">暂无订单</div>
<?php else: ?>
<?php foreach ($orders as $o): ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
        <span style="font-family:var(--font-mono);font-size:.85rem;"><?= h($o['order_code']) ?></span>
        <span class="badge badge-<?= $badgeMap[$o['status']] ?? 'secondary' ?>"><?= $statusMap[$o['status']] ?? '未知' ?></span>
    </div>
    <div class="card-body">
        <?php
        $items = $pdo->prepare("SELECT oi.*, p.name as product_name FROM product_order_item oi LEFT JOIN product p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $items->execute([$o['id']]);
        foreach ($items->fetchAll() as $item):
        ?>
        <p class="mb-1"><?= h($item['product_name']) ?> x<?= $item['number'] ?> <span style="color:var(--accent);font-family:var(--font-mono);">&yen;<?= format_price($item['price']) ?></span></p>
        <?php endforeach; ?>
        <hr>
        <p class="mb-0">
            合计: <strong style="color:var(--accent);font-family:var(--font-mono);">&yen;<?= format_price($o['total_price']) ?></strong>
            <span style="color:var(--text-muted);margin-left:1rem;font-size:.85rem;"><?= $o['create_time'] ?></span>
            <?php if ($o['status'] == 0): ?>
            <a href="<?= base_url() ?>/index.php?action=order_pay&code=<?= h($o['order_code']) ?>" class="btn btn-sm btn-primary ml-2">去支付</a>
            <?php endif; ?>
        </p>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
