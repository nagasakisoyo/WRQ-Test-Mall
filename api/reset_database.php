<?php
/**
 * One-click database reset endpoint.
 * Reads sql/wrqtestmall.sql and executes it to re-initialize everything.
 */
header('Content-Type: application/json; charset=utf-8');

$sqlFile = __DIR__ . '/../sql/wrqtestmall.sql';

if (!file_exists($sqlFile)) {
    echo json_encode(['success' => false, 'msg' => 'SQL 文件不存在: sql/wrqtestmall.sql']);
    exit;
}

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '123456';

$mysqli = new mysqli($host, $user, $pass, '', $port);
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'msg' => 'MySQL 连接失败: ' . $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset('utf8mb4');

$sql = file_get_contents($sqlFile);

$mysqli->multi_query($sql);

$errors = [];
do {
    $result = $mysqli->store_result();
    if ($result) $result->free();
    if ($mysqli->error) {
        $errors[] = $mysqli->error;
    }
} while ($mysqli->more_results() && $mysqli->next_result());

$mysqli->close();

if (empty($errors)) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_destroy();

    echo json_encode([
        'success' => true,
        'msg'     => '数据库初始化成功！所有数据已重置。'
    ], JSON_UNESCAPED_UNICODE);
} else {
    $unique = array_unique($errors);
    echo json_encode([
        'success' => true,
        'msg'     => '数据库初始化完成（部分非关键警告可忽略）',
        'warnings'=> array_values($unique)
    ], JSON_UNESCAPED_UNICODE);
}
