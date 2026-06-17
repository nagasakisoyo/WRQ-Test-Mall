<?php
$page_title = '商品详情';
$id = intval($_GET['id'] ?? 0);

$product = get_product($id);
if (!$product || $product['is_enabled'] != 0) {
    echo '<div class="alert alert-danger">商品不存在</div>';
    return;
}

$pdo = get_pdo();
$reviews = $pdo->prepare("SELECT r.*, u.nickname as user_nickname FROM review r LEFT JOIN user u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.create_time DESC");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$page_title = $product['name'];
$extra_js = ['product_detail.js'];
include __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-5">
        <div class="bg-light text-center py-5 rounded">
            <span style="font-size:8rem;">📦</span>
        </div>
    </div>
    <div class="col-md-7">
        <h3><?= h($product['name']) ?></h3>
        <p class="text-muted"><?= h($product['title']) ?></p>
        <h4 class="text-danger">¥<?= format_price($product['sale_price']) ?>
            <small class="text-muted"><del>¥<?= format_price($product['price']) ?></del></small>
        </h4>
        <p>分类：<span class="badge badge-info"><?= h($product['category_name']) ?></span></p>
        <p>库存：<?= $product['stock'] ?> 件</p>

        <div class="mt-3">
            <button id="btn-expand-info" class="btn btn-outline-secondary" data-product-id="<?= $product['id'] ?>">
                ▼ 展开更多信息
            </button>
        </div>
        <div id="expand-info-area" class="mt-3 p-3 bg-light rounded" style="display:none;">
            <div id="expand-info-content">加载中...</div>
        </div>

        <?php if (is_user_logged_in()): ?>
        <hr>
        <form id="addCartForm" class="form-inline">
            <label class="mr-2">数量：</label>
            <input type="number" name="number" value="1" min="1" max="99" class="form-control mr-2" style="width:80px;">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <button type="submit" class="btn btn-warning mr-2">加入购物车</button>
            <button type="button" id="btnBuyNow" class="btn btn-danger">立即购买</button>
        </form>
        <?php else: ?>
        <hr>
        <a href="<?= base_url() ?>/index.php?action=login" class="btn btn-primary">登录后购买</a>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4">
    <h5>商品描述</h5>
    <div class="p-3 bg-light rounded"><?= nl2br(h($product['description'])) ?></div>
</div>

<div class="mt-4">
    <h5>商品评价 (<?= count($reviews) ?>)</h5>
    <?php if (empty($reviews)): ?>
        <p class="text-muted">暂无评价</p>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
        <div class="border-bottom py-2">
            <strong><?= h($r['user_nickname']) ?></strong>
            <small class="text-muted ml-2"><?= $r['create_time'] ?></small>
            <p class="mb-0 mt-1"><?= h($r['content']) ?></p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
var BASE_URL = '<?= base_url() ?>';

$('#addCartForm').on('submit', function(e) {
    e.preventDefault();
    $.post(BASE_URL + '/api/cart.php?action=add', $(this).serialize(), function(res) {
        if (res.success) { alert('已添加到购物车'); } else { alert(res.msg || '操作失败'); }
    }, 'json');
});

$('#btnBuyNow').on('click', function() {
    var num = $('input[name=number]').val();
    var pid = <?= $product['id'] ?>;
    window.location.href = BASE_URL + '/index.php?action=order_confirm&product_id=' + pid + '&number=' + num;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
