<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // VULN-006: weak password admin/admin123, MD5 hash comparison
    if ($admin && $admin['password'] === md5($password)) {
        admin_login($admin);
        json_response(['success' => true]);
    } else {
        json_response(['success' => false, 'msg' => '用户名或密码错误']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - WRQTestMall</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/style.css">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center"><h4 class="mb-0">WRQTestMall 后台管理</h4></div>
                <div class="card-body">
                    <div id="login-msg" class="alert d-none"></div>
                    <form id="adminLoginForm">
                        <div class="form-group">
                            <label>管理员账号</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>密码</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-dark btn-block">登录</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="<?= base_url() ?>/index.php">返回前台首页</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script>
$('#adminLoginForm').on('submit', function(e) {
    e.preventDefault();
    $.post('<?= base_url() ?>/admin/login.php', $(this).serialize(), function(res) {
        if (res.success) {
            window.location.href = '<?= base_url() ?>/admin/dashboard.php';
        } else {
            $('#login-msg').removeClass('d-none alert-success').addClass('alert-danger').text(res.msg);
        }
    }, 'json');
});
</script>
</body>
</html>
