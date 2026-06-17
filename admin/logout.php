<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';
admin_logout();
header('Location: ' . base_url() . '/admin/login.php');
exit;
