<?php
$page_title = '商品列表';
$pdo = get_pdo();

$cid = intval($_GET['cid'] ?? 0);
$keyword = trim($_GET['keyword'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where = "WHERE is_enabled = 0";
$params = [];

if ($cid > 0) {
    $where .= " AND category_id = ?";
    $params[] = $cid;
}
if ($keyword !== '') {
    $where .= " AND (name LIKE ? OR title LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM product $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $limit));

$params[] = $offset;
$params[] = $limit;
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM product p LEFT JOIN category c ON p.category_id = c.id $where ORDER BY p.id DESC LIMIT ?, ?");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = get_categories();

include __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">商品分类</div>
            <div class="list-group list-group-flush">
                <a href="<?= base_url() ?>/index.php?action=products" class="list-group-item list-group-item-action <?= $cid === 0 ? 'active' : '' ?>">全部商品</a>
                <?php foreach ($categories as $cat): ?>
                <a href="<?= base_url() ?>/index.php?action=products&cid=<?= $cat['id'] ?>" class="list-group-item list-group-item-action <?= $cid === $cat['id'] ? 'active' : '' ?>"><?= h($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <form class="mb-3" method="get" action="<?= base_url() ?>/index.php">
            <input type="hidden" name="action" value="products">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="搜索商品..." value="<?= h($keyword) ?>">
                <div class="input-group-append"><button class="btn btn-primary">搜索</button></div>
            </div>
        </form>

        <?php if (empty($products)): ?>
            <div class="alert alert-info">暂无相关商品</div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($products as $prod): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 product-card">
                    <div class="card-img-top text-center py-4" style="background:var(--bg-elevated);font-size:2.5rem;opacity:.5;">&#128230;</div>
                    <div class="card-body">
                        <h6 style="font-family:var(--font-display);font-weight:600;"><?= h($prod['name']) ?></h6>
                        <p style="color:var(--text-muted);font-size:.82rem;margin-bottom:.5rem;"><?= h($prod['title']) ?></p>
                        <span class="badge badge-secondary"><?= h($prod['category_name']) ?></span>
                        <p class="mt-2 mb-0">
                            <span class="price-current" style="font-size:1.05rem;">&yen;<?= format_price($prod['sale_price']) ?></span>
                            <small class="price-original ml-1"><del>&yen;<?= format_price($prod['price']) ?></del></small>
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="<?= base_url() ?>/index.php?action=product&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary btn-block">查看详情</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav><ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= base_url() ?>/index.php?action=products&cid=<?= $cid ?>&keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
