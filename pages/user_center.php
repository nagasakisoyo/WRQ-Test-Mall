<?php
$page_title = '个人中心';
$userId = get_current_user_id();
$extra_js = ['user_center.js'];
include __DIR__ . '/../includes/header.php';
?>

<h3>个人中心</h3>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">个人信息</div>
            <div class="card-body">
                <div id="profile-msg" class="alert d-none"></div>
                <div id="profile-loading">加载中...</div>
                <div id="profile-content" style="display:none;">
                    <table class="table table-bordered">
                        <tr><th width="120">用户名</th><td id="p-username"></td></tr>
                        <tr><th>昵称</th><td id="p-nickname"></td></tr>
                        <tr><th>真实姓名</th><td id="p-realname"></td></tr>
                        <tr><th>性别</th><td id="p-gender"></td></tr>
                        <tr><th>生日</th><td id="p-birthday"></td></tr>
                        <tr><th>手机</th><td id="p-phone"></td></tr>
                        <tr><th>邮箱</th><td id="p-email"></td></tr>
                        <tr><th>地址</th><td id="p-address"></td></tr>
                        <tr><th>注册时间</th><td id="p-createtime"></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">快捷操作</div>
            <div class="card-body">
                <a href="<?= base_url() ?>/index.php?action=orders" class="btn btn-outline-primary btn-block mb-2">我的订单</a>
                <a href="<?= base_url() ?>/index.php?action=cart" class="btn btn-outline-warning btn-block mb-2">购物车</a>
                <a href="<?= base_url() ?>/index.php?action=logout" class="btn btn-outline-danger btn-block">退出登录</a>
            </div>
        </div>
    </div>
</div>

<!-- VULN-003: uid taken from hidden field, AJAX fetches any user's data -->
<input type="hidden" id="current-uid" value="<?= $userId ?>">

<?php include __DIR__ . '/../includes/footer.php'; ?>
