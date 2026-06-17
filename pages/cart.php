<?php
$page_title = '购物车';
$pdo = get_pdo();
$userId = get_current_user_id();

$stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.sale_price, p.title as product_title 
    FROM product_order_item oi 
    LEFT JOIN product p ON oi.product_id = p.id 
    WHERE oi.user_id = ? AND oi.order_id IS NULL ORDER BY oi.id DESC");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h3 style="font-family:var(--font-display);margin-bottom:1.5rem;">购物车</h3>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info">购物车为空。<a href="<?= base_url() ?>/index.php?action=products">去逛逛</a></div>
<?php else: ?>
<table class="table cart-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="checkAll" checked></th>
            <th>商品</th>
            <th>单价</th>
            <th>数量</th>
            <th>小计</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cartItems as $item): ?>
        <tr data-item-id="<?= $item['id'] ?>">
            <td><input type="checkbox" class="item-check" value="<?= $item['id'] ?>" checked></td>
            <td>
                <a href="<?= base_url() ?>/index.php?action=product&id=<?= $item['product_id'] ?>"><?= h($item['product_name']) ?></a>
                <small class="d-block" style="color:var(--text-muted);"><?= h($item['product_title']) ?></small>
            </td>
            <td style="color:var(--accent);font-family:var(--font-mono);">&yen;<?= format_price($item['sale_price']) ?></td>
            <td>
                <input type="number" class="form-control form-control-sm item-num" value="<?= $item['number'] ?>" min="1" max="99" style="width:70px;" data-id="<?= $item['id'] ?>">
            </td>
            <td class="item-subtotal" style="color:var(--accent);font-family:var(--font-mono);font-weight:600;">&yen;<?= format_price($item['price']) ?></td>
            <td><button class="btn btn-sm btn-outline-danger btn-del-item" data-id="<?= $item['id'] ?>">删除</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="text-right">
    <h5>合计: <span id="cart-total" style="color:var(--accent);font-family:var(--font-mono);">&yen;0.00</span></h5>
    <button id="btnCheckout" class="btn btn-primary btn-lg mt-2">去结算</button>
</div>

<script>
var BASE_URL = '<?= base_url() ?>';

function calcTotal() {
    var total = 0;
    $('.item-check:checked').each(function() {
        var row = $(this).closest('tr');
        total += parseFloat(row.find('.item-subtotal').text().replace('¥',''));
    });
    $('#cart-total').text('¥' + total.toFixed(2));
}
calcTotal();

$('#checkAll').on('change', function() {
    $('.item-check').prop('checked', this.checked);
    calcTotal();
});
$('.item-check').on('change', calcTotal);

$('.item-num').on('change', function() {
    var id = $(this).data('id'), num = $(this).val();
    $.post(BASE_URL + '/api/cart.php?action=update', {item_id: id, number: num}, function(res) {
        if (res.success) location.reload();
    }, 'json');
});

$('.btn-del-item').on('click', function() {
    if (!confirm('确定删除该商品？')) return;
    $.post(BASE_URL + '/api/cart.php?action=delete', {item_id: $(this).data('id')}, function(res) {
        if (res.success) location.reload();
    }, 'json');
});

$('#btnCheckout').on('click', function() {
    var ids = [];
    $('.item-check:checked').each(function() { ids.push($(this).val()); });
    if (ids.length === 0) { alert('请选择商品'); return; }
    window.location.href = BASE_URL + '/index.php?action=order_confirm&items=' + ids.join(',');
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
