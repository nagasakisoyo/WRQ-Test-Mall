/**
 * Product detail page — "展开更多信息" button handler
 * Sends AJAX to api/product_detail.php (VULN-001: SQL injection endpoint)
 * Renders product_detail_ext data (spec_params, long_desc, origin, warranty, etc.)
 */
$(document).ready(function() {
    var BASE_URL = '';
    var scripts = document.getElementsByTagName('script');
    for (var i = 0; i < scripts.length; i++) {
        var src = scripts[i].src || '';
        var idx = src.indexOf('/assets/js/');
        if (idx !== -1) { BASE_URL = src.substring(0, idx); break; }
    }

    var expanded = false;

    $('#btn-expand-info').on('click', function() {
        var btn = $(this);
        var productId = btn.data('product-id');

        if (expanded) {
            $('#expand-info-area').slideUp();
            btn.html('▼ 展开更多信息');
            expanded = false;
            return;
        }

        btn.prop('disabled', true).text('加载中...');

        $.ajax({
            url: BASE_URL + '/api/product_detail.php',
            type: 'GET',
            data: { id: productId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var p = res.product;
                    var html = '<table class="table table-sm table-bordered mb-3">';

                    if (res.properties && res.properties.length > 0) {
                        html += '<thead><tr><th colspan="2" class="bg-primary text-white">基本规格</th></tr></thead><tbody>';
                        for (var i = 0; i < res.properties.length; i++) {
                            html += '<tr><th width="120">' + (res.properties[i].prop_name || '') + '</th>';
                            html += '<td>' + (res.properties[i].prop_value || '-') + '</td></tr>';
                        }
                        html += '</tbody>';
                    }

                    if (p.spec_params) {
                        html += '<thead><tr><th colspan="2" class="bg-info text-white">详细参数</th></tr></thead><tbody>';
                        try {
                            var specs = JSON.parse(p.spec_params);
                            for (var key in specs) {
                                if (specs.hasOwnProperty(key)) {
                                    html += '<tr><th width="120">' + key + '</th><td>' + specs[key] + '</td></tr>';
                                }
                            }
                        } catch(e) {
                            html += '<tr><td colspan="2">' + p.spec_params + '</td></tr>';
                        }
                        html += '</tbody>';
                    }

                    html += '</table>';

                    if (p.long_desc) {
                        html += '<div class="mb-3"><h6>详细介绍</h6><p>' + p.long_desc + '</p></div>';
                    }

                    var extraInfo = '';
                    if (p.origin)       extraInfo += '<span class="badge badge-secondary mr-2">产地: ' + p.origin + '</span>';
                    if (p.warranty)     extraInfo += '<span class="badge badge-secondary mr-2">质保: ' + p.warranty + '</span>';
                    if (p.package_list) extraInfo += '<div class="mt-2"><strong>包装清单:</strong> ' + p.package_list + '</div>';
                    if (p.after_service)extraInfo += '<div class="mt-1"><strong>售后服务:</strong> ' + p.after_service + '</div>';

                    if (extraInfo) {
                        html += '<div class="p-2 bg-white border rounded">' + extraInfo + '</div>';
                    }

                    $('#expand-info-content').html(html);
                    $('#expand-info-area').slideDown();
                    btn.html('▲ 收起更多信息');
                    expanded = true;
                } else {
                    $('#expand-info-content').html('<div class="text-danger">加载失败: ' + (res.msg || '') + '</div>');
                    $('#expand-info-area').slideDown();
                }
            },
            error: function() {
                $('#expand-info-content').html('<div class="text-danger">网络请求失败</div>');
                $('#expand-info-area').slideDown();
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });
});
