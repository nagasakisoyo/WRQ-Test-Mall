<?php
$page_title = '订单支付';
$code = $_GET['code'] ?? '';
$pdo = get_pdo();
$userId = get_current_user_id();

$stmt = $pdo->prepare("SELECT * FROM product_order WHERE order_code = ? AND user_id = ?");
$stmt->execute([$code, $userId]);
$order = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<?php if (!$order): ?>
    <div class="alert alert-danger">订单不存在</div>
<?php else: ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning"><h5 class="mb-0">订单支付</h5></div>
            <div class="card-body text-center">
                <p>订单编号：<strong><?= h($order['order_code']) ?></strong></p>
                <h3 class="text-danger">¥<?= format_price($order['total_price']) ?></h3>
                <p class="text-muted">收货人：<?= h($order['receiver']) ?> | <?= h($order['mobile']) ?></p>
                <p class="text-muted"><?= h($order['address_detail']) ?></p>
                <hr>
                <?php if ($order['status'] == 0): ?>
                <button class="btn btn-danger btn-lg" id="btnPay" data-code="<?= h($order['order_code']) ?>">确认支付</button>
                <?php else: ?>
                <div class="alert alert-success">订单已支付</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$('#btnPay').on('click', function() {
    $.post('<?= base_url() ?>/api/order_pay.php', {order_code: $(this).data('code')}, function(res) {
        if (res.success) {
            alert('支付成功！');
            location.reload();
        } else {
            alert(res.msg || '支付失败');
        }
    }, 'json');
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
