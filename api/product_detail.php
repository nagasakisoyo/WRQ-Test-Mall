<?php
/**
 * VULN-001: SQL Injection via string concatenation
 *
 * Uses mysqli::multi_query() to support stacked queries.
 * Combined with MySQL root + secure_file_priv="" this enables sqlmap --os-shell.
 *
 * This endpoint powers the "展开更多信息" button on the product detail page.
 * It queries both the product table and the product_detail_ext table.
 */
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$id = $_GET['id'] ?? '';

$mysqli = get_mysqli();

// VULN: $id is directly concatenated — no escaping, no parameterization
$sql = "SELECT p.id, p.name, p.title, p.price, p.sale_price, p.stock, p.description, "
     . "c.name AS category_name, "
     . "ext.spec_params, ext.long_desc, ext.origin, ext.warranty, ext.package_list, ext.after_service "
     . "FROM product p "
     . "LEFT JOIN category c ON p.category_id = c.id "
     . "LEFT JOIN product_detail_ext ext ON ext.product_id = p.id "
     . "WHERE p.id = " . $id;

$result = $mysqli->multi_query($sql);

if ($result) {
    $res = $mysqli->store_result();
    if ($res) {
        $product = $res->fetch_assoc();
        $res->free();

        while ($mysqli->more_results() && $mysqli->next_result()) {
            $extra = $mysqli->store_result();
            if ($extra) $extra->free();
        }

        if ($product) {
            $props = [];
            $pid = intval($product['id']);
            $pstmt = $mysqli->query(
                "SELECT pr.name AS prop_name, pv.value AS prop_value "
                . "FROM property_value pv "
                . "LEFT JOIN property pr ON pv.property_id = pr.id "
                . "WHERE pv.product_id = " . $pid
            );
            if ($pstmt) {
                while ($row = $pstmt->fetch_assoc()) {
                    $props[] = $row;
                }
                $pstmt->free();
            }

            echo json_encode([
                'success'    => true,
                'product'    => $product,
                'properties' => $props
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'msg' => 'product not found']);
        }
    } else {
        echo json_encode(['success' => false, 'msg' => $mysqli->error]);
    }
} else {
    echo json_encode(['success' => false, 'msg' => $mysqli->error]);
}
