<?php
$page_title = '用户注册';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white"><h5 class="mb-0">用户注册</h5></div>
            <div class="card-body">
                <div id="reg-msg" class="alert d-none"></div>
                <form id="regForm">
                    <div class="form-group">
                        <label>用户名 <small class="text-muted">(登录账号)</small></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>昵称</label>
                        <input type="text" name="nickname" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>密保问题</label>
                            <input type="text" name="security_question" class="form-control" placeholder="如：你的出生城市？" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>密保答案</label>
                            <input type="text" name="security_answer" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">注册</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="<?= base_url() ?>/index.php?action=login">已有账号？去登录</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$('#regForm').on('submit', function(e) {
    e.preventDefault();
    $.post('<?= base_url() ?>/index.php?action=do_register', $(this).serialize(), function(res) {
        if (res.success) {
            alert('注册成功！请登录');
            window.location.href = '<?= base_url() ?>/index.php?action=login';
        } else {
            $('#reg-msg').removeClass('d-none alert-success').addClass('alert-danger').text(res.msg);
        }
    }, 'json');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
