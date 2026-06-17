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
    <div class="col-md-5 col-lg-4">
        <div class="card" style="border-color:var(--border-accent);">
            <div class="card-header" style="background:var(--accent-muted);border-bottom-color:var(--border-accent);">
                <h5 class="mb-0" style="color:var(--accent);">订单支付</h5>
            </div>
            <div class="card-body text-center py-4">
                <p style="font-size:.9rem;color:var(--text-muted);">订单编号: <strong style="font-family:var(--font-mono);color:var(--text-primary);"><?= h($order['order_code']) ?></strong></p>
                <h3 style="color:var(--accent);font-family:var(--font-mono);font-weight:700;font-size:2rem;margin:1rem 0;">&yen;<?= format_price($order['total_price']) ?></h3>
                <p style="font-size:.85rem;color:var(--text-muted);">
                    <?= h($order['receiver']) ?> | <?= h($order['mobile']) ?><br>
                    <?= h($order['address_detail']) ?>
                </p>
                <hr>
                <?php if ($order['status'] == 0): ?>
                <button class="btn btn-primary btn-lg px-5" id="btnPay" data-code="<?= h($order['order_code']) ?>">确认支付</button>
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
