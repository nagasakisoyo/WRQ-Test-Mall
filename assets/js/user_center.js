/**
 * User center page — loads profile via AJAX
 * VULN-003: uid comes from hidden field, can be tampered to view other users
 */
$(document).ready(function() {
    var BASE_URL = '';
    var scripts = document.getElementsByTagName('script');
    for (var i = 0; i < scripts.length; i++) {
        var src = scripts[i].src || '';
        var idx = src.indexOf('/assets/js/');
        if (idx !== -1) { BASE_URL = src.substring(0, idx); break; }
    }

    var uid = $('#current-uid').val();

    $.ajax({
        url: BASE_URL + '/api/user_profile.php',
        type: 'GET',
        data: { uid: uid },
        dataType: 'json',
        success: function(res) {
            if (res.success && res.data) {
                var d = res.data;
                var genderMap = {'0': '未知', '1': '男', '2': '女'};
                $('#p-username').text(d.username || '-');
                $('#p-nickname').text(d.nickname || '-');
                $('#p-realname').text(d.realname || '-');
                $('#p-gender').text(genderMap[d.gender] || '未知');
                $('#p-birthday').text(d.birthday || '-');
                $('#p-phone').text(d.phone || '-');
                $('#p-email').text(d.email || '-');
                $('#p-address').text(d.address || '-');
                $('#p-createtime').text(d.create_time || '-');
                $('#profile-loading').hide();
                $('#profile-content').show();
            } else {
                $('#profile-loading').text('加载失败');
            }
        },
        error: function() {
            $('#profile-loading').text('网络错误');
        }
    });
});
