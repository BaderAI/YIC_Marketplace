<?php
require_once 'classes/Auth.php';
require_once 'classes/Listing.php';
require_once 'classes/Message.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

Auth::requireLogin();

$user_id = Auth::userId();

function status_class($status)
{
    $status = strtolower((string) $status);
    if ($status === 'approved') {
        return 'approved';
    }
    if ($status === 'rejected') {
        return 'rejected';
    }
    return 'pending';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_action'], $_POST['order_id'])) {
    try {
        Auth::requireValidCsrf($_POST['csrf_token'] ?? '');

        $postData = normalizeNumericFields($_POST, [
            'order_id' => false,
        ]);
        $order_id = (int) $postData['order_id'];
        $action = $_POST['order_action'];

        $check_stmt = $pdo->prepare(
            'SELECT po.*, i.seller_id, i.quantity AS stock_qty
             FROM purchase_orders po
             INNER JOIN items i ON po.item_id = i.item_id
             WHERE po.order_id = ?'
        );
        $check_stmt->execute([$order_id]);
        $order = $check_stmt->fetch();

        if (!$order || (int) $order['seller_id'] !== (int) $user_id || $order['status'] !== 'Pending') {
            header('Location: purchase_orders.php?msg=unauthorized#orders-section');
            exit();
        }

        if ($action === 'approve') {
            if ((int) $order['stock_qty'] < (int) $order['quantity']) {
                header('Location: purchase_orders.php?error=insufficient_stock#orders-section');
                exit();
            }

            $pdo->beginTransaction();
            $update_order = $pdo->prepare("UPDATE purchase_orders SET status = 'Approved' WHERE order_id = ?");
            $update_order->execute([$order_id]);

            $new_qty = (int) $order['stock_qty'] - (int) $order['quantity'];
            $update_stock = $pdo->prepare('UPDATE items SET quantity = ? WHERE item_id = ?');
            $update_stock->execute([$new_qty, (int) $order['item_id']]);
            $pdo->commit();

            header('Location: purchase_orders.php?msg=approved#orders-section');
            exit();
        }

        if ($action === 'reject') {
            $update_order = $pdo->prepare("UPDATE purchase_orders SET status = 'Rejected' WHERE order_id = ?");
            $update_order->execute([$order_id]);

            header('Location: purchase_orders.php?msg=rejected#orders-section');
            exit();
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die('Error processing request: ' . e($e->getMessage()));
    }
}

try {
    $sql_orders = "SELECT po.*, i.title AS item_title, i.price AS item_price, u.name AS buyer_name
                   FROM purchase_orders po
                   INNER JOIN items i ON po.item_id = i.item_id
                   INNER JOIN users u ON po.buyer_id = u.user_id
                   WHERE i.seller_id = ?
                   ORDER BY po.created_at DESC";
    $stmt_orders = $pdo->prepare($sql_orders);
    $stmt_orders->execute([$user_id]);
    $incoming_orders = $stmt_orders->fetchAll();

    $messageModel = new Message($pdo);
    $messages = $messageModel->inboxForUser($user_id);

    $listingModel = new Listing($pdo);
    $my_items = $listingModel->getBySeller($user_id);
} catch (PDOException $e) {
    die('Database Error: ' . e($e->getMessage()));
}

$page_title = 'Sales & Orders';
include 'includes/header.php';
?>

<main class="container orders-page">
    <section id="orders-section" class="panel-card">
        <h2>Incoming Purchase Orders</h2>
        <p class="muted-note">Review and accept purchase requests from other students for your products.</p>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'approved'): ?>
            <div class="alert success">Request approved. Stock reduced successfully.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'rejected'): ?>
            <div class="alert warning">Request rejected successfully.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
            <div class="alert error">You are not allowed to process that order.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'insufficient_stock'): ?>
            <div class="alert error">Cannot approve: available stock is lower than requested quantity.</div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Buyer Name</th>
                    <th>Requested Qty</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($incoming_orders): ?>
                    <?php foreach ($incoming_orders as $order): ?>
                        <tr>
                            <td><strong><?php echo e($order['item_title']); ?></strong></td>
                            <td><?php echo e($order['buyer_name']); ?></td>
                            <td><strong><?php echo formatEnglishInteger($order['quantity']); ?> units</strong></td>
                            <td class="money-cell"><?php echo formatEnglishNumber((float) $order['item_price'] * (int) $order['quantity'], 2); ?> SAR</td>
                            <td><span class="status-badge <?php echo e(status_class($order['status'])); ?>"><?php echo e($order['status']); ?></span></td>
                            <td>
                                <?php if ($order['status'] === 'Pending'): ?>
                                    <div class="table-actions">
                                        <form action="purchase_orders.php#orders-section" method="POST" class="inline-action-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
                                            <input type="hidden" name="order_id" value="<?php echo formatEnglishInteger($order['order_id']); ?>">
                                            <input type="hidden" name="order_action" value="approve">
                                            <button type="submit" class="small-btn success-btn js-confirm-action" data-confirm="Confirming this will reduce your item stock. Agree?">Approve</button>
                                        </form>
                                        <form action="purchase_orders.php#orders-section" method="POST" class="inline-action-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
                                            <input type="hidden" name="order_id" value="<?php echo formatEnglishInteger($order['order_id']); ?>">
                                            <input type="hidden" name="order_action" value="reject">
                                            <button type="submit" class="small-btn danger-btn js-confirm-action" data-confirm="Reject this purchase request?">Reject</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="muted-note">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-table-message">You have not received any purchase orders yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section id="inbox-section" class="panel-card">
        <h2>My Inbox</h2>
        <p class="muted-note">Inquiries and messages from students regarding your marketplace items.</p>

        <table class="data-table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Regarding Item</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($messages): ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><strong><?php echo e($msg['sender_name']); ?></strong></td>
                            <td><?php echo e($msg['item_title']); ?></td>
                            <td><?php echo e($msg['message_text']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="empty-table-message">Your inbox is empty. No messages received yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section id="items-section" class="panel-card">
        <h3>My Listed Items</h3>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert success">Item deleted successfully.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert success">Item updated successfully.</div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Product Title</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_items): ?>
                    <?php foreach ($my_items as $item): ?>
                        <tr>
                            <td><a href="item_details.php?id=<?php echo formatEnglishInteger($item['item_id']); ?>"><?php echo e($item['title']); ?></a></td>
                            <td class="money-cell"><?php echo formatEnglishNumber($item['price'], 2); ?> SAR</td>
                            <td><strong><?php echo formatEnglishInteger($item['quantity']); ?> units</strong></td>
                            <td>
                                <div class="table-actions">
                                    <a href="edit_item.php?id=<?php echo formatEnglishInteger($item['item_id']); ?>&redirect=orders" class="small-btn edit-btn">Update</a>
                                    <form action="delete_item.php" method="POST" class="inline-action-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
                                        <input type="hidden" name="item_id" value="<?php echo formatEnglishInteger($item['item_id']); ?>">
                                        <input type="hidden" name="redirect" value="orders">
                                        <button type="submit" class="small-btn danger-btn js-confirm-delete" data-confirm="Are you sure you want to delete this item listing?">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-table-message">You have not listed any items for sale yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
