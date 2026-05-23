<?php
require_once 'classes/Auth.php';
require_once 'classes/Listing.php';
require_once 'includes/functions.php';

Auth::requireLogin();

$item_id = (int) normalizeNumericInput($_GET['id'] ?? 0);
$listing = new Listing();
$item = $listing->findWithSeller($item_id);

if (!$item) {
    die("<p class='container'>Item not found.</p>");
}

$is_owner = (int) $item['seller_id'] === (int) Auth::userId();

$page_title = 'Item Details';
include 'includes/header.php';
?>

<main class="details-page">
    <section class="details-card">
        <div class="details-image">
            <h2><?php echo e($item['title']); ?></h2>
            <img src="<?php echo e(item_image_src($item['image_path'])); ?>" alt="<?php echo e($item['title']); ?>">
        </div>

        <div class="details-info">
            <p><strong>Category:</strong> <?php echo e($item['cat_name']); ?></p>
            <p><strong>Price:</strong> <span class="price-highlight"><?php echo formatEnglishNumber($item['price'], 2); ?> SAR</span></p>
            <p><strong>Available Stock:</strong> <span class="stock-highlight"><?php echo formatEnglishInteger($item['quantity']); ?> units</span></p>
            <p><strong>Seller:</strong> <?php echo e($item['seller_name']); ?></p>
            <p><strong>Description:</strong></p>
            <p class="description-box"><?php echo nl2br(e($item['description'])); ?></p>
        </div>
    </section>

    <section class="details-action-card">
        <h3>Send Purchase Request</h3>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'order_placed'): ?>
            <p class="notice success-text">Your purchase request has been submitted and is awaiting seller approval.</p>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'sent'): ?>
            <p class="notice success-text">Your message was sent to the seller.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'out_of_stock'): ?>
            <p class="notice error-text">Requested quantity exceeds available stock.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'empty_message'): ?>
            <p class="notice error-text">Please write a message before sending.</p>
        <?php endif; ?>

        <?php if (!$is_owner): ?>
            <?php if ((int) $item['quantity'] > 0): ?>
                <form action="process_purchase_request.php" method="POST" class="compact-form">
                    <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
                    <input type="hidden" name="item_id" value="<?php echo formatEnglishInteger($item['item_id']); ?>">

                    <label for="req_quantity">Required Quantity</label>
                    <input type="text" id="req_quantity" name="req_quantity" value="<?php echo formatEnglishInteger(1); ?>" min="1" max="<?php echo formatEnglishInteger($item['quantity']); ?>" step="1" inputmode="numeric" data-numeric="integer" pattern="[0-9]+" autocomplete="off" required>

                    <button type="submit" class="success-btn">Place Purchase Order</button>
                </form>
            <?php else: ?>
                <p class="out-of-stock">Out of Stock</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted-note">You own this item listing.</p>
        <?php endif; ?>
    </section>

    <?php if (!$is_owner): ?>
        <section class="details-action-card">
            <h3>Contact Seller</h3>
            <form action="send_message.php" method="POST" class="compact-form" id="message-form">
                <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
                <input type="hidden" name="item_id" value="<?php echo formatEnglishInteger($item['item_id']); ?>">

                <label for="message_text">Message</label>
                <textarea id="message_text" name="message_text" rows="4" placeholder="Ask about pickup time, condition, or availability." required></textarea>

                <div class="form-message" aria-live="polite"></div>
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </section>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
