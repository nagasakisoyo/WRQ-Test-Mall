<?php
$page_title = '找回密码';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="auth-card card">
            <div class="card-header">
                <h5>重置密码</h5>
            </div>
            <div class="card-body p-4">
                <div id="forgot-msg" class="alert d-none"></div>

                <div id="step1">
                    <div class="form-group">
                        <label>请输入用户名</label>
                        <input type="text" id="forgot-username" class="form-control" placeholder="用户名" required>
                    </div>
                    <button id="btn-step1" class="btn btn-primary btn-block">下一步</button>
                </div>

                <div id="step2" style="display:none;">
                    <div class="form-group">
                        <label>密保问题</label>
                        <p id="security-question" style="color:var(--accent);font-weight:600;"></p>
                    </div>
                    <div class="form-group">
                        <label>你的答案</label>
                        <input type="text" id="security-answer" class="form-control" placeholder="请输入密保答案" required>
                    </div>
                    <button id="btn-step2" class="btn btn-primary btn-block">验证</button>
                </div>

                <div id="step3" style="display:none;">
                    <div class="form-group">
                        <label>新密码</label>
                        <input type="password" id="new-password" class="form-control" placeholder="请输入新密码" required>
                    </div>
                    <div class="form-group">
                        <label>确认密码</label>
                        <input type="password" id="confirm-password" class="form-control" placeholder="再次输入新密码" required>
                    </div>
                    <button id="btn-step3" class="btn btn-primary btn-block">重置密码</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var BASE_URL = '<?= base_url() ?>';

$('#btn-step1').on('click', function() {
    var u = $('#forgot-username').val();
    if (!u) return;
    $.post(BASE_URL + '/api/forgot_password.php', {step: 'check_user', username: u}, function(res) {
        if (res.success) {
            $('#security-question').text(res.question);
            $('#step1').hide();
            $('#step2').show();
        } else {
            $('#forgot-msg').removeClass('d-none alert-success').addClass('alert-danger').text(res.msg);
        }
    }, 'json');
});

$('#btn-step2').on('click', function() {
    var a = $('#security-answer').val();
    $.post(BASE_URL + '/api/forgot_password.php', {step: 'verify_answer', answer: a}, function(res) {
        if (res.success) {
            $('#step2').hide();
            $('#step3').show();
        } else {
            $('#forgot-msg').removeClass('d-none alert-success').addClass('alert-danger').text(res.msg);
        }
    }, 'json');
});

$('#btn-step3').on('click', function() {
    var p1 = $('#new-password').val(), p2 = $('#confirm-password').val();
    if (p1 !== p2) { alert('两次密码不一致'); return; }
    $.post(BASE_URL + '/api/forgot_password.php', {step: 'reset_password', new_password: p1}, function(res) {
        if (res.success) {
            alert('密码重置成功，请登录');
            window.location.href = BASE_URL + '/index.php?action=login';
        } else {
            alert(res.msg);
        }
    }, 'json');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
