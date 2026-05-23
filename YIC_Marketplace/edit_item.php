<?php
require_once 'classes/Auth.php';
require_once 'classes/Listing.php';
require_once 'classes/Category.php';
require_once 'includes/functions.php';

Auth::requireLogin();

$item_id = (int) normalizeNumericInput($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? '';

$listing = new Listing();
$item = $listing->findById($item_id);

if (!$item) {
    die('Error: Item not found.');
}

if (!$listing->canManage($item, Auth::userId(), Auth::role())) {
    die('Access Denied: You do not have permission to edit this item.');
}

$categoryModel = new Category();
$categories = $categoryModel->getAll();

$page_title = 'Edit Listing';
include 'includes/header.php';
?>

<main>
    <section class="form-section">
        <h2>Edit Listing</h2>
        <?php echo form_flash(); ?>

        <form action="process_edit_item.php" method="POST" enctype="multipart/form-data" id="edit-item-form">
            <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">
            <input type="hidden" name="item_id" value="<?php echo formatEnglishInteger($item['item_id']); ?>">
            <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">

            <fieldset>
                <legend>Item Information</legend>

                <label for="title">Item Title</label>
                <input type="text" id="title" name="title" required maxlength="150" value="<?php echo e($item['title']); ?>">

                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo formatEnglishInteger($category['cat_id']); ?>" <?php echo (int) $item['cat_id'] === (int) $category['cat_id'] ? 'selected' : ''; ?>>
                            <?php echo e($category['cat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="price">Price (SAR)</label>
                <input type="text" id="price" name="price" required min="0.01" step="0.01" inputmode="decimal" data-numeric="decimal" pattern="[0-9]+([.][0-9]+)?" autocomplete="off" value="<?php echo e(toEnglishDigits($item['price'])); ?>">

                <label for="quantity">Quantity Available</label>
                <input type="text" id="quantity" name="quantity" required min="0" step="1" inputmode="numeric" data-numeric="integer" pattern="[0-9]+" autocomplete="off" value="<?php echo formatEnglishInteger($item['quantity']); ?>">

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?php echo e($item['description']); ?></textarea>

                <label for="image">Update Photo (Optional)</label>
                <div class="custom-file-upload">
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" class="native-file-input">
                    <label for="image" class="file-upload-btn">Choose File</label>
                    <span class="file-upload-name" data-default="No file chosen">No file chosen</span>
                </div>
                <?php if (!empty($item['image_path'])): ?>
                    <p class="current-image-info">Current image: <?php echo e($item['image_path']); ?></p>
                <?php endif; ?>
            </fieldset>

            <div class="form-message" aria-live="polite"></div>
            <button type="submit" class="submit-btn">Update Listing</button>
        </form>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
