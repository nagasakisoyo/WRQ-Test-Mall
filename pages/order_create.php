<?php
$page_title = '确认订单';
$pdo = get_pdo();
$userId = get_current_user_id();

$orderItems = [];
$totalPrice = 0;

if (isset($_GET['product_id'])) {
    $pid = intval($_GET['product_id']);
    $num = max(1, intval($_GET['number'] ?? 1));
    $product = get_product($pid);
    if ($product) {
        $subtotal = $product['sale_price'] * $num;
        $orderItems[] = ['product_id' => $pid, 'name' => $product['name'], 'sale_price' => $product['sale_price'], 'number' => $num, 'subtotal' => $subtotal];
        $totalPrice = $subtotal;
    }
} elseif (isset($_GET['items'])) {
    $itemIds = array_map('intval', explode(',', $_GET['items']));
    foreach ($itemIds as $iid) {
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.sale_price FROM product_order_item oi LEFT JOIN product p ON oi.product_id = p.id WHERE oi.id = ? AND oi.user_id = ? AND oi.order_id IS NULL");
        $stmt->execute([$iid, $userId]);
        $item = $stmt->fetch();
        if ($item) {
            $orderItems[] = ['product_id' => $item['product_id'], 'name' => $item['product_name'], 'sale_price' => $item['sale_price'], 'number' => $item['number'], 'subtotal' => $item['price'], 'cart_item_id' => $item['id']];
            $totalPrice += $item['price'];
        }
    }
}

$user = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$user->execute([$userId]);
$user = $user->fetch();

$extra_js = ['order.js'];
include __DIR__ . '/../includes/header.php';
?>

<h3 style="font-family:var(--font-display);margin-bottom:1.5rem;">确认订单</h3>

<?php if (empty($orderItems)): ?>
    <div class="alert alert-warning">没有可结算的商品</div>
<?php else: ?>
<div class="card mb-3">
    <div class="card-header">订单商品</div>
    <table class="table mb-0">
        <thead><tr><th>商品</th><th>单价</th><th>数量</th><th>小计</th></tr></thead>
        <tbody>
        <?php foreach ($orderItems as $item): ?>
        <tr>
            <td><?= h($item['name']) ?></td>
            <td style="font-family:var(--font-mono);">&yen;<?= format_price($item['sale_price']) ?></td>
            <td><?= $item['number'] ?></td>
            <td style="color:var(--accent);font-family:var(--font-mono);font-weight:600;">&yen;<?= format_price($item['subtotal']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card mb-3">
    <div class="card-header">收货信息</div>
    <div class="card-body">
        <form id="orderForm">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>收货人</label>
                    <input type="text" name="receiver" class="form-control" value="<?= h($user['realname'] ?? '') ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label>手机号</label>
                    <input type="text" name="mobile" class="form-control" value="<?= h($user['phone'] ?? '') ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label>邮编</label>
                    <input type="text" name="post_code" class="form-control" value="100000">
                </div>
            </div>
            <div class="form-group">
                <label>详细地址</label>
                <input type="text" name="address_detail" class="form-control" value="<?= h($user['address'] ?? '') ?>" required>
            </div>

            <!-- VULN-004: total_price sent from client -->
            <input type="hidden" name="total_price" id="total_price" value="<?= format_price($totalPrice) ?>">
            <input type="hidden" name="items" value='<?= json_encode($orderItems) ?>'>

            <div class="text-right">
                <h4>应付金额: <span style="color:var(--accent);font-family:var(--font-mono);font-weight:700;" id="display-total">&yen;<?= format_price($totalPrice) ?></span></h4>
                <button type="submit" class="btn btn-primary btn-lg mt-2">提交订单</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
