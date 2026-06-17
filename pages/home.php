<?php
$page_title = '首页';

// Gracefully handle database not yet initialized
$db_ok = true;
try {
    $pdo = get_pdo();
    $pdo->query("SELECT 1 FROM product LIMIT 1");
} catch (Exception $e) {
    $db_ok = false;
}

if (!$db_ok) {
    // Database doesn't exist or tables missing — show install page
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="text-center py-5">
        <h1 class="display-4 text-primary mb-4">WRQTestMall 靶场</h1>
        <div class="alert alert-warning d-inline-block" style="max-width:600px;">
            <h5>⚠ 数据库尚未初始化</h5>
            <p>检测到数据库 <code>wrqtestmall</code> 不存在或表结构缺失。<br>点击下方按钮一键初始化数据库和测试数据。</p>
        </div>
        <br><br>
        <button id="btn-init-db" class="btn btn-danger btn-lg px-5">
            🚀 初始化数据库
        </button>
        <div id="init-result" class="mt-3"></div>
    </div>
    <script>
    document.getElementById('btn-init-db').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.textContent = '正在初始化，请稍候...';
        document.getElementById('init-result').innerHTML = '<div class="spinner-border text-primary"></div>';

        fetch('<?= base_url() ?>/api/reset_database.php', {method: 'POST'})
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    document.getElementById('init-result').innerHTML =
                        '<div class="alert alert-success">' + res.msg + '<br><br>页面将在 2 秒后刷新...</div>';
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    document.getElementById('init-result').innerHTML =
                        '<div class="alert alert-danger">' + res.msg + '</div>';
                    btn.disabled = false;
                    btn.textContent = '🚀 重试初始化';
                }
            })
            .catch(function(err) {
                document.getElementById('init-result').innerHTML =
                    '<div class="alert alert-danger">请求失败: ' + err + '</div>';
                btn.disabled = false;
                btn.textContent = '🚀 重试初始化';
            });
    });
    </script>
    <?php
    include __DIR__ . '/../includes/footer.php';
    return;
}

// ---- Normal homepage below ----

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
    <strong>公告：</strong>
    <?php foreach ($announcements as $ann): ?>
        <span class="mr-3"><?= $ann['title'] ?> —— <?= $ann['admin_nickname'] ?></span>
    <?php endforeach; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php endif; ?>

<div id="heroCarousel" class="carousel slide mb-4" data-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="bg-primary text-white text-center py-5 rounded">
                <h1>WRQTestMall 618 大促</h1>
                <p class="lead">全场商品限时优惠，满300减50！</p>
                <a href="<?= base_url() ?>/index.php?action=products" class="btn btn-light btn-lg">立即选购</a>
            </div>
        </div>
        <div class="carousel-item">
            <div class="bg-success text-white text-center py-5 rounded">
                <h1>新品上架</h1>
                <p class="lead">锤子手机 T3 震撼发布</p>
                <a href="<?= base_url() ?>/index.php?action=product&id=1" class="btn btn-light btn-lg">查看详情</a>
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
            <div class="card-img-top bg-light text-center py-4">
                <span class="text-muted" style="font-size:3rem;">📦</span>
            </div>
            <div class="card-body">
                <h6 class="card-title"><?= h($prod['name']) ?></h6>
                <p class="text-muted small"><?= h($prod['title']) ?></p>
                <p class="text-danger font-weight-bold">¥<?= format_price($prod['sale_price']) ?>
                    <small class="text-muted"><del>¥<?= format_price($prod['price']) ?></del></small>
                </p>
            </div>
            <div class="card-footer bg-white">
                <a href="<?= base_url() ?>/index.php?action=product&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary btn-block">查看详情</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php endforeach; ?>

<!-- ===== 靶场管理面板 ===== -->
<hr class="mt-5">
<div class="card border-danger mt-3 mb-4">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center" id="labPanelToggle" style="cursor:pointer;">
        <span>🔧 靶场管理面板</span>
        <small>点击展开/收起</small>
    </div>
    <div class="card-body" id="labPanelBody" style="display:none;">
        <div class="row">
            <div class="col-md-6">
                <h6>数据库重置</h6>
                <p class="text-muted small">将数据库恢复到初始状态：清除所有修改，重新导入全部测试数据。适用于首次使用或练习后恢复环境。</p>
                <button id="btn-reset-db" class="btn btn-outline-danger">
                    🔄 重置数据库
                </button>
                <div id="reset-result" class="mt-2"></div>
            </div>
            <div class="col-md-6">
                <h6>默认账号速查</h6>
                <table class="table table-sm table-bordered small">
                    <tr><td><strong>管理员后台</strong></td><td><a href="<?= base_url() ?>/admin/login.php">admin/login.php</a></td></tr>
                    <tr><td>admin</td><td>admin123</td></tr>
                    <tr><td>manager</td><td>manager888</td></tr>
                    <tr class="table-active"><td><strong>前台用户</strong></td><td>密码统一 <code>123456</code></td></tr>
                    <tr><td>zhangwei</td><td>123456</td></tr>
                    <tr><td>lina</td><td>123456</td></tr>
                    <tr><td colspan="2" class="text-muted">...共20个用户，详见项目文件树.txt</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$('#labPanelToggle').on('click', function() {
    $('#labPanelBody').slideToggle(200);
});

$('#btn-reset-db').on('click', function() {
    if (!confirm('⚠ 确定要重置数据库吗？\n\n将清空所有数据（包括你上传的文件记录、新建的订单等），恢复到初始测试数据。\n\n此操作不可撤销！')) return;

    var btn = $(this);
    btn.prop('disabled', true).text('正在重置...');
    $('#reset-result').html('<div class="spinner-border spinner-border-sm text-danger"></div> 执行中，请稍候...');

    $.ajax({
        url: '<?= base_url() ?>/api/reset_database.php',
        type: 'POST',
        dataType: 'json',
        timeout: 30000,
        success: function(res) {
            if (res.success) {
                $('#reset-result').html('<div class="alert alert-success py-2">' + res.msg + '</div>');
                setTimeout(function() { location.reload(); }, 2000);
            } else {
                $('#reset-result').html('<div class="alert alert-danger py-2">' + res.msg + '</div>');
                btn.prop('disabled', false).text('🔄 重试');
            }
        },
        error: function(xhr, status, err) {
            $('#reset-result').html('<div class="alert alert-danger py-2">请求失败: ' + (err || status) + '</div>');
            btn.prop('disabled', false).text('🔄 重试');
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
