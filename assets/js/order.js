/**
 * Order creation page
 * VULN-004: total_price is submitted from the client-side hidden field
 */
$(document).ready(function() {
    var BASE_URL = '';
    var scripts = document.getElementsByTagName('script');
    for (var i = 0; i < scripts.length; i++) {
        var src = scripts[i].src || '';
        var idx = src.indexOf('/assets/js/');
        if (idx !== -1) { BASE_URL = src.substring(0, idx); break; }
    }

    $('#orderForm').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
            url: BASE_URL + '/api/order_create.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    window.location.href = BASE_URL + '/index.php?action=order_pay&code=' + res.order_code;
                } else {
                    alert(res.msg || '订单创建失败');
                }
            },
            error: function() {
                alert('网络错误');
            }
        });
    });
});
