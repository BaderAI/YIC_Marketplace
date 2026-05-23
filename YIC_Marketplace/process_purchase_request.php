<?php
require_once 'classes/Auth.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$postData = normalizeNumericFields($_POST, [
    'item_id' => false,
    'req_quantity' => false,
]);

$item_id = (int) ($postData['item_id'] ?? 0);
$buyer_id = (int) Auth::userId();
$req_quantity = (int) ($postData['req_quantity'] ?? 0);

try {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

    if ($req_quantity < 1) {
        header('Location: item_details.php?id=' . $item_id . '&error=out_of_stock');
        exit();
    }

    $stmt = $pdo->prepare('SELECT quantity, seller_id FROM items WHERE item_id = ?');
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if (!$item || (int) $item['seller_id'] === $buyer_id || (int) $item['quantity'] < $req_quantity) {
        header('Location: item_details.php?id=' . $item_id . '&error=out_of_stock');
        exit();
    }

    $ins_stmt = $pdo->prepare("INSERT INTO purchase_orders (item_id, buyer_id, quantity, status) VALUES (?, ?, ?, 'Pending')");
    $ins_stmt->execute([$item_id, $buyer_id, $req_quantity]);

    header('Location: item_details.php?id=' . $item_id . '&msg=order_placed');
    exit();
} catch (PDOException $e) {
    die('Database Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
