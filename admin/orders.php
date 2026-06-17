<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin_login();

$pdo = get_pdo();
$statusMap = [0 => '待付款', 1 => '待发货', 2 => '待收货', 3 => '已完成', 4 => '已关闭'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    $oid = intval($_POST['order_id'] ?? 0);
    if ($act === 'ship' && $oid > 0) {
        $pdo->prepare("UPDATE product_order SET status = 2, delivery_date = NOW() WHERE id = ? AND status = 1")->execute([$oid]);
    } elseif ($act === 'close' && $oid > 0) {
        $pdo->prepare("UPDATE product_order SET status = 4 WHERE id = ?")->execute([$oid]);
    }
    header('Location: ' . base_url() . '/admin/orders.php');
    exit;
}

$page_title = '订单管理';
include __DIR__ . '/../includes/admin_header.php';

$orders = $pdo->query("SELECT o.*, u.username FROM product_order o LEFT JOIN user u ON o.user_id = u.id ORDER BY o.create_time DESC")->fetchAll();
?>

<h3>订单管理</h3>
<table class="table table-bordered table-hover">
    <thead class="thead-dark">
        <tr><th>订单号</th><th>用户</th><th>金额</th><th>收货人</th><th>状态</th><th>创建时间</th><th>操作</th></tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <tr>
        <td><?= h($o['order_code']) ?></td>
        <td><?= h($o['username']) ?></td>
        <td class="text-danger">¥<?= format_price($o['total_price']) ?></td>
        <td><?= h($o['receiver']) ?></td>
        <td><?= $statusMap[$o['status']] ?? '未知' ?></td>
        <td><?= $o['create_time'] ?></td>
        <td>
            <?php if ($o['status'] == 1): ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="act" value="ship">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                <button class="btn btn-sm btn-success">发货</button>
            </form>
            <?php endif; ?>
            <?php if ($o['status'] < 3): ?>
            <form method="post" style="display:inline;" onsubmit="return confirm('确定关闭此订单？')">
                <input type="hidden" name="act" value="close">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">关闭</button>
            </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
