<?php
require_once 'classes/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

if (!Auth::validateCsrf($_POST['csrf_token'] ?? '')) {
    header('Location: login.php?error=csrf');
    exit();
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (Auth::login($email, $password)) {
    header('Location: dashboard.php');
    exit();
}

header('Location: login.php?error=invalid_credentials');
exit();
