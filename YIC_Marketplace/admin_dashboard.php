<?php
require_once 'classes/Auth.php';
require_once 'classes/Admin.php';
require_once 'includes/functions.php';

Auth::requireAdmin();

$admin = new Admin();
$stats = $admin->dashboardStats();
$top_items = $admin->topStockItems(5);

$page_title = 'Admin Dashboard';
include 'includes/header.php';
?>

<main class="container admin-dashboard">
    <div class="page-heading">
        <h2>YIC Marketplace Live Statistics</h2>
        <p>Real-time overview of database records, store inventory, and user metrics.</p>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert success">Listing deleted successfully.</div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card blue-stat">
            <h4>Total Members</h4>
            <p><?php echo formatEnglishInteger($stats['count_users']); ?></p>
        </div>

        <div class="stat-card green-stat">
            <h4>Active Listings</h4>
            <p><?php echo formatEnglishInteger($stats['count_items']); ?></p>
        </div>

        <div class="stat-card yellow-stat">
            <h4>Total Market Stock</h4>
            <p><?php echo formatEnglishInteger($stats['total_stock']); ?> units</p>
        </div>

        <div class="stat-card orange-stat">
            <h4>Market Valuation</h4>
            <p><?php echo formatEnglishNumber($stats['total_value'], 2); ?> <span>SAR</span></p>
        </div>
    </div>

    <section class="panel-card">
        <h3>Top Stock Availability Items</h3>
        <table class="data-table dashboard-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Unit Price</th>
                    <th>Available Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($top_items): ?>
                    <?php foreach ($top_items as $top): ?>
                        <tr>
                            <td><strong><?php echo e($top['title']); ?></strong></td>
                            <td class="money-cell"><?php echo formatEnglishNumber($top['price'], 2); ?> SAR</td>
                            <td class="stock-cell"><?php echo formatEnglishInteger($top['quantity']); ?> units</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="empty-table-message">No store inventory records analyzed yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
