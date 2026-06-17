/**
 * Admin avatar upload — frontend-only validation
 * VULN-002: Only JS checks file extension/MIME; server accepts anything
 */
$(document).ready(function() {
    var BASE_URL = '';
    var scripts = document.getElementsByTagName('script');
    for (var i = 0; i < scripts.length; i++) {
        var src = scripts[i].src || '';
        var idx = src.indexOf('/assets/js/');
        if (idx !== -1) { BASE_URL = src.substring(0, idx); break; }
    }

    function checkFile(file) {
        var allowExt = ['jpg', 'jpeg', 'png', 'gif'];
        var ext = file.name.split('.').pop().toLowerCase();
        if (allowExt.indexOf(ext) === -1) {
            alert('仅允许上传 jpg/png/gif 格式的图片！');
            return false;
        }
        var allowMime = ['image/jpeg', 'image/png', 'image/gif'];
        if (allowMime.indexOf(file.type) === -1) {
            alert('文件类型不正确！');
            return false;
        }
        return true;
    }

    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        var fileInput = $('#avatarFile')[0];
        if (!fileInput.files || !fileInput.files[0]) {
            alert('请选择文件');
            return;
        }
        var file = fileInput.files[0];
        if (!checkFile(file)) return;

        var formData = new FormData();
        formData.append('avatar', file);

        $.ajax({
            url: BASE_URL + '/admin/upload.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('上传成功！');
                    $('#avatarSrcInput').val(res.fileUrl);
                    $('#avatarSrcForm').submit();
                } else {
                    alert(res.msg || '上传失败');
                }
            },
            error: function() {
                alert('上传出错');
            }
        });
    });
});
