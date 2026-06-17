<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin_login();

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add') {
        $stmt = $pdo->prepare("INSERT INTO product (name, title, price, sale_price, category_id, stock, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['title'], $_POST['price'], $_POST['sale_price'], $_POST['category_id'], $_POST['stock'], $_POST['description']]);
    } elseif ($act === 'toggle') {
        $id = intval($_POST['id']);
        $cur = $pdo->prepare("SELECT is_enabled FROM product WHERE id = ?");
        $cur->execute([$id]);
        $curVal = $cur->fetchColumn();
        $new = $curVal == 0 ? 1 : 0;
        $pdo->prepare("UPDATE product SET is_enabled = ? WHERE id = ?")->execute([$new, $id]);
    } elseif ($act === 'delete') {
        $pdo->prepare("DELETE FROM product WHERE id = ?")->execute([intval($_POST['id'])]);
    }
    header('Location: ' . base_url() . '/admin/products.php');
    exit;
}

$page_title = '商品管理';
include __DIR__ . '/../includes/admin_header.php';

$products = $pdo->query("SELECT p.*, c.name as category_name FROM product p LEFT JOIN category c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
$categories = get_categories();
?>

<h3 style="font-family:var(--font-display);margin-bottom:1.5rem;">商品管理</h3>
<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">添加商品</button>

<table class="table table-hover">
    <thead>
        <tr><th>ID</th><th>商品名</th><th>分类</th><th>原价</th><th>售价</th><th>库存</th><th>状态</th><th>操作</th></tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
    <tr>
        <td style="font-family:var(--font-mono);font-size:.85rem;"><?= $p['id'] ?></td>
        <td><?= h($p['name']) ?></td>
        <td><span class="badge badge-info"><?= h($p['category_name']) ?></span></td>
        <td style="font-family:var(--font-mono);">&yen;<?= format_price($p['price']) ?></td>
        <td style="font-family:var(--font-mono);color:var(--accent);">&yen;<?= format_price($p['sale_price']) ?></td>
        <td style="font-family:var(--font-mono);"><?= $p['stock'] ?></td>
        <td><?= $p['is_enabled'] == 0 ? '<span class="badge badge-success">上架</span>' : '<span class="badge badge-secondary">下架</span>' ?></td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="act" value="toggle">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button class="btn btn-sm btn-outline-warning"><?= $p['is_enabled'] == 0 ? '下架' : '上架' ?></button>
            </form>
            <form method="post" style="display:inline;" onsubmit="return confirm('确定删除？')">
                <input type="hidden" name="act" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">删除</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5>添加商品</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <form method="post">
        <div class="modal-body">
            <input type="hidden" name="act" value="add">
            <div class="form-group"><label>商品名</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label>副标题</label><input type="text" name="title" class="form-control"></div>
            <div class="form-row">
                <div class="form-group col"><label>原价</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                <div class="form-group col"><label>售价</label><input type="number" step="0.01" name="sale_price" class="form-control" required></div>
                <div class="form-group col"><label>库存</label><input type="number" name="stock" class="form-control" value="999"></div>
            </div>
            <div class="form-group"><label>分类</label>
                <select name="category_id" class="form-control">
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>描述</label><textarea name="description" class="form-control" rows="3"></textarea></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">添加</button></div>
        </form>
    </div></div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
