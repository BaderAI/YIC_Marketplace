<?php
require_once 'classes/Auth.php';
require_once 'classes/Listing.php';
require_once 'includes/functions.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit();
}

Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

$postData = normalizeNumericFields($_POST, [
    'item_id' => false,
]);

$listing = new Listing();
$listing->markSold((int) ($postData['item_id'] ?? 0), Auth::userId());

header('Location: profile.php?msg=updated');
exit();
