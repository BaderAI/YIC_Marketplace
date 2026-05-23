<?php
require_once 'classes/Listing.php';
require_once 'classes/Category.php';
require_once 'includes/functions.php';

$search = trim($_GET['q'] ?? '');
$selected_category = (int) normalizeNumericInput($_GET['category'] ?? 0);

try {
    $listingModel = new Listing();
    $categoryModel = new Category();
    $items = $listingModel->getAvailable($search, $selected_category);
    $categories = $categoryModel->getAll();
} catch (PDOException $e) {
    die('Error fetching data: ' . e($e->getMessage()));
}

$page_title = 'YIC Marketplace - Home';
include 'includes/header.php';
?>

<main>
    <section class="hero">
        <h1>Campus Marketplace</h1>
        <p>Find what you need from your fellow YIC students.</p>
    </section>

    <section class="marketplace-filter-bar">
        <form id="marketplace-search-form" class="search-filter-form" action="index.php" method="GET">
            <div class="filter-control search-control">
                <label for="live-search">Search items</label>
                <input type="search" id="live-search" name="q" value="<?php echo e($search); ?>" placeholder="Search by item, description, or category">
            </div>

            <div class="filter-control category-control">
                <label for="category-filter">Category</label>
                <select id="category-filter" name="category">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo formatEnglishInteger($category['cat_id']); ?>" <?php echo $selected_category === (int) $category['cat_id'] ? 'selected' : ''; ?>>
                            <?php echo e($category['cat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-control button-control">
                <button type="button" id="toggle-filters" class="filter-btn" aria-expanded="true">Filters</button>
            </div>
        </form>
        <p id="search-status" class="search-status" aria-live="polite"></p>
    </section>

    <section id="marketplace-results" class="grid-container">
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <?php echo render_item_card($item); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-grid-message">No items available for sale right now.</p>
        <?php endif; ?>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
