<?php
require_once 'classes/Auth.php';
require_once 'classes/Listing.php';
require_once 'includes/functions.php';

Auth::requireLogin();

function delete_redirect_target($redirect, $message)
{
    $targets = [
        'management' => 'admin_management.php',
        'admin' => 'admin_dashboard.php',
        'orders' => 'purchase_orders.php#items-section',
        'profile' => 'profile.php',
    ];

    $target = $targets[$redirect] ?? 'profile.php';
    if (strpos($target, '#') !== false) {
        [$base, $hash] = explode('#', $target, 2);
        return $base . '?msg=' . urlencode($message) . '#' . $hash;
    }

    return $target . '?msg=' . urlencode($message);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php?msg=invalid_request');
    exit();
}

$redirect = $_POST['redirect'] ?? 'profile';

try {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

    $listing = new Listing();
    $postData = normalizeNumericFields($_POST, [
        'item_id' => false,
    ]);
    $listing->delete((int) ($postData['item_id'] ?? 0), Auth::userId(), Auth::role());

    header('Location: ' . delete_redirect_target($redirect, 'deleted'));
    exit();
} catch (Exception $e) {
    header('Location: ' . delete_redirect_target($redirect, 'unauthorized'));
    exit();
}
