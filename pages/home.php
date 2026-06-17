<?php
$page_title = '首页';

$db_ok = true;
try {
    $pdo = get_pdo();
    $pdo->query("SELECT 1 FROM product LIMIT 1");
} catch (Exception $e) {
    $db_ok = false;
}

if (!$db_ok) {
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="text-center py-5">
        <div style="font-size:3rem;margin-bottom:1rem;opacity:.6;">&#9881;</div>
        <h1 class="mb-3" style="font-size:2rem;">WRQTestMall</h1>
        <p class="mb-4" style="color:var(--text-secondary);max-width:420px;margin:0 auto;">
            数据库 <code>wrqtestmall</code> 未找到或数据表缺失。<br>
            点击下方按钮初始化数据库并生成测试数据。
        </p>
        <button id="btn-init-db" class="btn btn-primary btn-lg px-5">
            初始化数据库
        </button>
        <div id="init-result" class="mt-3" style="max-width:500px;margin:0 auto;"></div>
    </div>
    <script>
    document.getElementById('btn-init-db').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.textContent = '正在初始化...';
        document.getElementById('init-result').innerHTML = '<div class="spinner-border spinner-border-sm"></div>';

        fetch('<?= base_url() ?>/api/reset_database.php', {method: 'POST'})
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    document.getElementById('init-result').innerHTML =
                        '<div class="alert alert-success mt-3">' + res.msg + '</div>';
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    document.getElementById('init-result').innerHTML =
                        '<div class="alert alert-danger mt-3">' + res.msg + '</div>';
                    btn.disabled = false;
                    btn.textContent = '重试';
                }
            })
            .catch(function(err) {
                document.getElementById('init-result').innerHTML =
                    '<div class="alert alert-danger mt-3">请求失败: ' + err + '</div>';
                btn.disabled = false;
                btn.textContent = '重试';
            });
    });
    </script>
    <?php
    include __DIR__ . '/../includes/footer.php';
    return;
}

$announcements = $pdo->query("SELECT a.*, ad.nickname as admin_nickname FROM announcement a LEFT JOIN admin ad ON a.admin_id = ad.id WHERE a.is_visible = 1 ORDER BY a.create_time DESC LIMIT 5")->fetchAll();

$categories = get_categories();
$featured = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT * FROM product WHERE category_id = ? AND is_enabled = 0 ORDER BY id DESC LIMIT 4");
    $stmt->execute([$cat['id']]);
    $featured[$cat['id']] = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>

<?php if ($announcements): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <strong style="margin-right:.5rem;">公告:</strong>
    <?php foreach ($announcements as $ann): ?>
        <span class="mr-3"><?= h($ann['title']) ?></span>
    <?php endforeach; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php endif; ?>

<div class="carousel slide mb-4" id="heroCarousel" data-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="py-5 rounded text-center hero-slide" style="background:linear-gradient(135deg, rgba(16,185,129,.15), var(--bg-surface));border:1px solid var(--border);">
                <h1 style="color:var(--accent);">WRQTestMall</h1>
                <p class="lead" style="color:var(--text-secondary);">Web安全渗透测试训练平台，内含真实漏洞</p>
                <a href="<?= base_url() ?>/index.php?action=products" class="btn btn-primary btn-lg">浏览商品</a>
            </div>
        </div>
        <div class="carousel-item">
            <div class="py-5 rounded text-center hero-slide" style="background:linear-gradient(135deg, rgba(59,130,246,.1), var(--bg-surface));border:1px solid var(--border);">
                <h1 style="color:var(--text-primary);">8大OWASP漏洞</h1>
                <p class="lead" style="color:var(--text-secondary);">SQL注入、文件上传、越权访问、XSS等</p>
                <a href="<?= base_url() ?>/index.php?action=product&id=1" class="btn btn-outline-primary btn-lg">查看详情</a>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#heroCarousel" data-slide="prev"><span class="carousel-control-prev-icon"></span></a>
    <a class="carousel-control-next" href="#heroCarousel" data-slide="next"><span class="carousel-control-next-icon"></span></a>
</div>

<?php foreach ($categories as $cat): ?>
<?php if (!empty($featured[$cat['id']])): ?>
<h4 class="mt-4 mb-3"><?= h($cat['name']) ?></h4>
<div class="row">
    <?php foreach ($featured[$cat['id']] as $prod): ?>
    <div class="col-md-3 mb-3">
        <div class="card h-100 product-card">
            <div class="card-img-top text-center py-4" style="background:var(--bg-elevated);font-size:2.5rem;opacity:.5;">&#128230;</div>
            <div class="card-body">
                <h6 class="card-title" style="font-family:var(--font-display);font-weight:600;"><?= h($prod['name']) ?></h6>
                <p style="color:var(--text-muted);font-size:.82rem;margin-bottom:.5rem;"><?= h($prod['title']) ?></p>
                <p class="mb-0">
                    <span class="price-current" style="font-size:1.1rem;">&yen;<?= format_price($prod['sale_price']) ?></span>
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
<?php endif; ?>
<?php endforeach; ?>

<div class="text-center mt-5 mb-4">
    <button id="btn-reset-db" class="btn btn-outline-danger btn-sm">重置数据库</button>
    <div id="reset-result" class="mt-2"></div>
</div>

<script>
document.getElementById('btn-reset-db').addEventListener('click', function() {
    if (!confirm('确定重置数据库？所有数据将恢复到初始状态，此操作不可撤销。')) return;
    var btn = this;
    btn.disabled = true;
    btn.textContent = '正在重置...';
    document.getElementById('reset-result').innerHTML = '<div class="spinner-border spinner-border-sm"></div>';

    fetch('<?= base_url() ?>/api/reset_database.php', {method: 'POST'})
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                document.getElementById('reset-result').innerHTML =
                    '<div class="alert alert-success py-1 small">' + res.msg + '</div>';
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                document.getElementById('reset-result').innerHTML =
                    '<div class="alert alert-danger py-1 small">' + res.msg + '</div>';
                btn.disabled = false;
                btn.textContent = '重试';
            }
        })
        .catch(function(err) {
            document.getElementById('reset-result').innerHTML =
                '<div class="alert alert-danger py-1 small">请求失败: ' + err + '</div>';
            btn.disabled = false;
            btn.textContent = '重试';
        });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
