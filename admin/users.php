<?php
$page_title = '用户管理';
include __DIR__ . '/../includes/admin_header.php';

$pdo = get_pdo();
$users = $pdo->query("SELECT * FROM user ORDER BY id ASC")->fetchAll();
$genderMap = [0 => '未知', 1 => '男', 2 => '女'];
?>

<h3>用户管理</h3>
<table class="table table-bordered table-hover">
    <thead class="thead-dark">
        <tr><th>ID</th><th>用户名</th><th>昵称</th><th>真实姓名</th><th>性别</th><th>手机</th><th>邮箱</th><th>注册时间</th></tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= h($u['username']) ?></td>
        <td><?= h($u['nickname']) ?></td>
        <td><?= h($u['realname']) ?></td>
        <td><?= $genderMap[$u['gender']] ?? '未知' ?></td>
        <td><?= h($u['phone']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td><?= $u['create_time'] ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
