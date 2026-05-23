<?php
require_once 'classes/Auth.php';
require_once 'classes/Message.php';
require_once 'includes/functions.php';

Auth::requireLogin();

$messageModel = new Message();
$messages = $messageModel->inboxForUser(Auth::userId());

$page_title = 'Inbox';
include 'includes/header.php';
?>

<main class="container margin-top">
    <section class="panel-card">
        <h2>Inbox</h2>
        <p class="muted-note">Messages from students about your marketplace items.</p>

        <table class="data-table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Item</th>
                    <th>Message</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($messages): ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><strong><?php echo e($msg['sender_name']); ?></strong></td>
                            <td><a href="item_details.php?id=<?php echo formatEnglishInteger($msg['item_id']); ?>"><?php echo e($msg['item_title']); ?></a></td>
                            <td><?php echo nl2br(e($msg['message_text'])); ?></td>
                            <td><small class="text-muted"><?php echo e(toEnglishDigits($msg['sent_at'])); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-table-message">No messages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
