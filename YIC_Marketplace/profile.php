<?php
require_once 'classes/Auth.php';
require_once 'classes/User.php';
require_once 'classes/Listing.php';
require_once 'includes/functions.php';

Auth::requireLogin();

$userModel = new User();
$listingModel = new Listing();
$user = $userModel->findById(Auth::userId());
$my_items = $listingModel->getBySeller(Auth::userId());

if (!$user) {
    die('User not found.');
}

$page_title = 'My Profile';
include 'includes/header.php';
?>

<main class="container profile-page">
    <section class="profile-card">
        <h2>My Profile Card</h2>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert success">Listing updated successfully.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert success">Listing deleted successfully.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
            <div class="alert error">You are not allowed to manage that listing.</div>
        <?php endif; ?>

        <div class="profile-lines">
            <p><strong>Full Name:</strong> <span><?php echo e($user['name']); ?></span></p>
            <p><strong>Email Address:</strong> <span><?php echo e($user['email']); ?></span></p>
            <p><strong>Student ID:</strong> <span><?php echo e(toEnglishDigits($user['student_id'] ?? 'N/A')); ?></span></p>
            <p><strong>Account Role:</strong> <span class="role-badge"><?php echo e($user['role']); ?></span></p>
        </div>

        <div class="profile-actions">
            <a href="purchase_orders.php" class="btn inline-btn">Go to Sales & Orders Management</a>
        </div>
    </section>

    <section class="panel-card" id="items-section">
        <h3>My Listed Items</h3>
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
                            <td><?php echo formatEnglishInteger($item['quantity']); ?> units</td>
                            <td>
                                <div class="table-actions">
                                    <a href="edit_item.php?id=<?php echo formatEnglishInteger($item['item_id']); ?>&redirect=profile" class="small-btn edit-btn">Update</a>
                                    <form action="delete_item.php" method="POST" class="inline-action-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
                                        <input type="hidden" name="item_id" value="<?php echo formatEnglishInteger($item['item_id']); ?>">
                                        <input type="hidden" name="redirect" value="profile">
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
