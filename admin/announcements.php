<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin_login();

$pdo = get_pdo();
$adminId = get_current_admin_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add') {
        $stmt = $pdo->prepare("INSERT INTO announcement (title, content, admin_id) VALUES (?, ?, ?)");
        $stmt->execute([h($_POST['title']), h($_POST['content']), $adminId]);
    } elseif ($act === 'delete') {
        $pdo->prepare("DELETE FROM announcement WHERE id = ?")->execute([intval($_POST['id'])]);
    }
    header('Location: ' . base_url() . '/admin/announcements.php');
    exit;
}

$page_title = '公告管理';
include __DIR__ . '/../includes/admin_header.php';

$announcements = $pdo->query("SELECT a.*, ad.nickname as admin_nickname FROM announcement a LEFT JOIN admin ad ON a.admin_id = ad.id ORDER BY a.create_time DESC")->fetchAll();
?>

<h3>公告管理</h3>
<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addAnnModal">发布公告</button>

<table class="table table-bordered">
    <thead class="thead-dark">
        <tr><th>ID</th><th>标题</th><th>内容</th><th>发布者</th><th>发布时间</th><th>操作</th></tr>
    </thead>
    <tbody>
    <?php foreach ($announcements as $ann): ?>
    <tr>
        <td><?= $ann['id'] ?></td>
        <td><?= h($ann['title']) ?></td>
        <td><?= h($ann['content']) ?></td>
        <!-- VULN-005: admin_nickname rendered without escaping — XSS trigger -->
        <td><?= $ann['admin_nickname'] ?></td>
        <td><?= $ann['create_time'] ?></td>
        <td>
            <form method="post" style="display:inline;" onsubmit="return confirm('确定删除？')">
                <input type="hidden" name="act" value="delete">
                <input type="hidden" name="id" value="<?= $ann['id'] ?>">
                <button class="btn btn-sm btn-danger">删除</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="addAnnModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5>发布公告</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <form method="post">
        <div class="modal-body">
            <input type="hidden" name="act" value="add">
            <div class="form-group"><label>标题</label><input type="text" name="title" class="form-control" required></div>
            <div class="form-group"><label>内容</label><textarea name="content" class="form-control" rows="4" required></textarea></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">发布</button></div>
        </form>
    </div></div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
