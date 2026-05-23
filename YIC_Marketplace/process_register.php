<?php
require_once 'classes/Auth.php';
require_once 'classes/User.php';
require_once 'includes/functions.php';

Auth::startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

try {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

    $postData = normalizeNumericFields($_POST, [
        'student_id' => false,
    ]);

    $user = new User();
    $user->register(
        $postData['name'] ?? '',
        $postData['email'] ?? '',
        $postData['student_id'] ?? '',
        $postData['password'] ?? '',
        $postData['confirm_password'] ?? ''
    );

    header('Location: login.php?msg=registered');
    exit();
} catch (Exception $e) {
    $_SESSION['form_error'] = $e->getMessage();
    header('Location: register.php');
    exit();
}
