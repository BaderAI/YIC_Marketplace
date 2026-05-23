<?php
require_once 'classes/Auth.php';
require_once 'classes/Listing.php';
require_once 'includes/functions.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_item.php');
    exit();
}

try {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

    $listing = new Listing();
    $imageName = $listing->saveUploadedImage($_FILES['image'] ?? null, true);
    $listingData = normalizeNumericFields($_POST, [
        'category' => false,
        'price' => true,
        'quantity' => false,
    ]);
    $listing->create(Auth::userId(), $listingData, $imageName);

    header('Location: dashboard.php?msg=item_added');
    exit();
} catch (Exception $e) {
    $_SESSION['form_error'] = $e->getMessage();
    header('Location: add_item.php');
    exit();
}
