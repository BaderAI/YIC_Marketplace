<?php
require_once 'classes/Auth.php';
require_once 'classes/Listing.php';
require_once 'includes/functions.php';

Auth::requireLogin();

function edit_redirect_target($redirect, $message = 'updated')
{
    $allowed = [
        'management' => 'admin_management.php',
        'admin' => 'admin_dashboard.php',
        'orders' => 'purchase_orders.php#items-section',
        'profile' => 'profile.php',
        '' => 'profile.php',
    ];

    $target = $allowed[$redirect] ?? 'profile.php';
    $separator = strpos($target, '?') === false && strpos($target, '#') === false ? '?' : '&';

    if (strpos($target, '#') !== false) {
        [$base, $hash] = explode('#', $target, 2);
        return $base . '?msg=' . urlencode($message) . '#' . $hash;
    }

    return $target . $separator . 'msg=' . urlencode($message);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit();
}

$postData = normalizeNumericFields($_POST, [
    'item_id' => false,
    'category' => false,
    'price' => true,
    'quantity' => false,
]);
$itemId = (int) ($postData['item_id'] ?? 0);
$redirect = $_POST['redirect'] ?? '';

try {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

    $listing = new Listing();
    $imageName = $listing->saveUploadedImage($_FILES['image'] ?? null, false);
    $listing->update($itemId, Auth::userId(), Auth::role(), $postData, $imageName);

    header('Location: ' . edit_redirect_target($redirect));
    exit();
} catch (Exception $e) {
    $_SESSION['form_error'] = $e->getMessage();
    header('Location: edit_item.php?id=' . $itemId . '&redirect=' . urlencode($redirect));
    exit();
}
