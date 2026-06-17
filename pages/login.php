<?php
$page_title = '用户登录';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="auth-card card">
            <div class="card-header">
                <h5>用户登录</h5>
            </div>
            <div class="card-body p-4">
                <div id="login-msg" class="alert d-none"></div>
                <form id="loginForm">
                    <div class="form-group">
                        <label>用户名</label>
                        <input type="text" name="username" class="form-control" placeholder="请输入用户名" required>
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input type="password" name="password" class="form-control" placeholder="请输入密码" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3">登录</button>
                </form>
                <div class="mt-3 text-center" style="font-size:.85rem;">
                    <a href="<?= base_url() ?>/index.php?action=register">注册账号</a>
                    <span class="mx-2" style="color:var(--text-muted);">|</span>
                    <a href="<?= base_url() ?>/index.php?action=forgot">忘记密码？</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$('#loginForm').on('submit', function(e) {
    e.preventDefault();
    $.post('<?= base_url() ?>/index.php?action=do_login', $(this).serialize(), function(res) {
        if (res.success) {
            window.location.href = '<?= base_url() ?>/index.php';
        } else {
            $('#login-msg').removeClass('d-none alert-success').addClass('alert-danger').text(res.msg);
        }
    }, 'json');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
