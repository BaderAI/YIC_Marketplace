<?php
require_once 'classes/Auth.php';
require_once 'classes/Message.php';
require_once 'includes/functions.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$postData = normalizeNumericFields($_POST, [
    'item_id' => false,
]);
$itemId = (int) ($postData['item_id'] ?? 0);

try {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

    $message = new Message();
    $message->sendInquiry($itemId, Auth::userId(), $_POST['message_text'] ?? '');

    header('Location: item_details.php?id=' . $itemId . '&msg=sent');
    exit();
} catch (Exception $e) {
    header('Location: item_details.php?id=' . $itemId . '&error=empty_message');
    exit();
}
