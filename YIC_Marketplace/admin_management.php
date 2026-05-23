<?php
require_once 'classes/Auth.php';
require_once 'classes/Admin.php';
require_once 'includes/functions.php';

Auth::requireAdmin();

$admin = new Admin();
$all_users = $admin->users();
$all_items = $admin->listings();

$page_title = 'Admin Management';
include 'includes/header.php';
?>

<main class="container admin-management">
    <h2 class="admin-title">System Administration & Management</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert success">Element deleted from database successfully.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'protected'): ?>
        <div class="alert error">Protected admin accounts cannot be deleted.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
        <div class="alert error">Action was not allowed.</div>
    <?php endif; ?>

    <section class="panel-card">
        <h3>Registered Users Management</h3>
        <table class="data-table admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Student ID</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_users as $user): ?>
                    <tr>
                        <td><?php echo formatEnglishInteger($user['user_id']); ?></td>
                        <td><strong><?php echo e($user['name']); ?></strong></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo e(toEnglishDigits($user['student_id'] ?? 'N/A')); ?></td>
                        <td><span class="role-badge"><?php echo e($user['role']); ?></span></td>
                        <td>
                            <?php if ($user['role'] !== 'admin'): ?>
                                <form action="delete_user.php" method="POST" class="inline-action-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo formatEnglishInteger($user['user_id']); ?>">
                                    <button type="submit" class="small-btn danger-btn js-confirm-delete" data-confirm="Are you sure you want to completely ban and delete this user?">Delete User</button>
                                </form>
                            <?php else: ?>
                                <span class="muted-note">Protected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="panel-card">
        <h3>All Marketplace Items Control</h3>
        <table class="data-table admin-table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Title</th>
                    <th>Seller</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_items as $item): ?>
                    <tr>
                        <td><?php echo formatEnglishInteger($item['item_id']); ?></td>
                        <td><strong><?php echo e($item['title']); ?></strong></td>
                        <td><?php echo e($item['seller_name'] ?? 'Unknown'); ?></td>
                        <td class="money-cell"><?php echo formatEnglishNumber($item['price'], 2); ?> SAR</td>
                        <td><?php echo formatEnglishInteger($item['quantity']); ?> units</td>
                        <td>
                            <div class="table-actions">
                                <a href="edit_item.php?id=<?php echo formatEnglishInteger($item['item_id']); ?>&redirect=management" class="small-btn edit-btn">Update</a>
                                <form action="delete_item.php" method="POST" class="inline-action-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
                                    <input type="hidden" name="item_id" value="<?php echo formatEnglishInteger($item['item_id']); ?>">
                                    <input type="hidden" name="redirect" value="management">
                                    <button type="submit" class="small-btn danger-btn js-confirm-delete" data-confirm="Force delete this item from marketplace?">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
