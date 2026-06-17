<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin_login();

$pdo = get_pdo();
$adminId = get_current_admin_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act'])) {
    $act = $_POST['act'];

    if ($act === 'update_nickname') {
        /**
         * VULN-005: Stored XSS via admin nickname
         * No sanitization - malicious script tags are stored as-is.
         */
        $nickname = $_POST['nickname'];
        $pdo->prepare("UPDATE admin SET nickname = ? WHERE id = ?")->execute([$nickname, $adminId]);
        $_SESSION['admin_nickname'] = $nickname;
        header('Location: ' . base_url() . '/admin/profile.php?msg=' . urlencode('昵称已更新'));
        exit;
    }

    if ($act === 'update_password') {
        $newPass = md5($_POST['new_password']);
        $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?")->execute([$newPass, $adminId]);
        header('Location: ' . base_url() . '/admin/profile.php?msg=' . urlencode('密码已更新'));
        exit;
    }

    if ($act === 'update_avatar') {
        $avatarSrc = $_POST['avatar_src'] ?? '';
        $pdo->prepare("UPDATE admin SET avatar_src = ? WHERE id = ?")->execute([$avatarSrc, $adminId]);
        $_SESSION['admin_avatar'] = $avatarSrc;
        header('Location: ' . base_url() . '/admin/profile.php?msg=' . urlencode('头像已更新'));
        exit;
    }
}

$admin = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$admin->execute([$adminId]);
$admin = $admin->fetch();

$page_title = '管理员资料';
$msg = $_GET['msg'] ?? '';
$extra_js = ['upload.js'];
include __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-success"><?= h($msg) ?></div>
<?php endif; ?>

<h3 style="font-family:var(--font-display);margin-bottom:1.5rem;">管理员资料</h3>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">头像管理</div>
            <div class="card-body text-center">
                <?php if ($admin['avatar_src']): ?>
                <img src="<?= $admin['avatar_src'] ?>" style="width:100px;height:100px;object-fit:cover;border-radius:var(--radius-md);border:2px solid var(--border);" class="mb-3" id="avatarPreview">
                <?php else: ?>
                <div style="width:100px;height:100px;background:var(--bg-elevated);border:2px solid var(--border);border-radius:var(--radius-md);display:inline-flex;align-items:center;justify-content:center;font-size:2.5rem;opacity:.5;" class="mb-3" id="avatarPreview">&#128100;</div>
                <?php endif; ?>

                <!-- VULN-002: Only frontend JS validation for file upload -->
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="file" name="avatar" id="avatarFile" class="form-control-file" accept="image/*">
                        <small style="color:var(--text-muted);">仅支持 jpg/png/gif 格式</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">上传头像</button>
                </form>
                <form method="post" id="avatarSrcForm" style="display:none;">
                    <input type="hidden" name="act" value="update_avatar">
                    <input type="hidden" name="avatar_src" id="avatarSrcInput">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">昵称修改</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="act" value="update_nickname">
                    <div class="form-group">
                        <label>当前昵称</label>
                        <input type="text" name="nickname" class="form-control" value="<?= $admin['nickname'] ?>">
                    </div>
                    <button type="submit" class="btn btn-outline-warning btn-sm">保存昵称</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">修改密码</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="act" value="update_password">
                    <div class="form-group">
                        <label>新密码</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-sm">修改密码</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
