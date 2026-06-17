<?php
$page_title = '忘记密码';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-info text-white"><h5 class="mb-0">忘记密码</h5></div>
            <div class="card-body">
                <div id="forgot-msg" class="alert d-none"></div>

                <!-- Step 1: Enter username -->
                <div id="step1">
                    <div class="form-group">
                        <label>请输入您的用户名</label>
                        <input type="text" id="forgot-username" class="form-control" required>
                    </div>
                    <button id="btn-step1" class="btn btn-info btn-block">下一步</button>
                </div>

                <!-- Step 2: Answer security question -->
                <div id="step2" style="display:none;">
                    <div class="form-group">
                        <label>密保问题</label>
                        <p id="security-question" class="font-weight-bold"></p>
                    </div>
                    <div class="form-group">
                        <label>请输入密保答案</label>
                        <input type="text" id="security-answer" class="form-control" required>
                    </div>
                    <button id="btn-step2" class="btn btn-info btn-block">验证</button>
                </div>

                <!-- Step 3: Reset password (VULN-008: reachable by tampering step2 response) -->
                <div id="step3" style="display:none;">
                    <div class="form-group">
                        <label>新密码</label>
                        <input type="password" id="new-password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>确认新密码</label>
                        <input type="password" id="confirm-password" class="form-control" required>
                    </div>
                    <button id="btn-step3" class="btn btn-success btn-block">重置密码</button>
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
            alert('密码重置成功！请登录');
            window.location.href = BASE_URL + '/index.php?action=login';
        } else {
            alert(res.msg);
        }
    }, 'json');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
