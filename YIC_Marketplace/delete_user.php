<?php
require_once 'classes/Auth.php';
require_once 'classes/User.php';
require_once 'includes/functions.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_management.php?msg=invalid_request');
    exit();
}

try {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

    $user = new User();
    $postData = normalizeNumericFields($_POST, [
        'user_id' => false,
    ]);
    $user->deleteNonAdmin((int) ($postData['user_id'] ?? 0), Auth::userId());

    header('Location: admin_management.php?msg=deleted');
    exit();
} catch (Exception $e) {
    header('Location: admin_management.php?msg=protected');
    exit();
}
