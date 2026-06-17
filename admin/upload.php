<?php
/**
 * VULN-002: Unrestricted file upload
 * 
 * Server does NOT validate file extension, MIME type, or content.
 * Files are saved to /uploads/avatars/ with a simple timestamp prefix.
 * PHP files uploaded here can be directly accessed and executed.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin_logged_in()) {
    echo json_encode(['success' => false, 'msg' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'msg' => '上传失败']);
    exit;
}

$file = $_FILES['avatar'];
$uploadDir = __DIR__ . '/../uploads/avatars/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = time() . '_' . $file['name'];
$targetPath = $uploadDir . $fileName;

move_uploaded_file($file['tmp_name'], $targetPath);

$fileUrl = SITE_URL . '/uploads/avatars/' . $fileName;

$pdo = get_pdo();
$stmt = $pdo->prepare("INSERT INTO upload_file (original_name, saved_name, file_path, file_size, mime_type, user_type, user_id) VALUES (?, ?, ?, ?, ?, 'admin', ?)");
$stmt->execute([$file['name'], $fileName, $targetPath, $file['size'], $file['type'], get_current_admin_id()]);

echo json_encode(['success' => true, 'fileUrl' => $fileUrl]);
