<?php
require_once 'classes/Auth.php';
require_once 'classes/Category.php';
require_once 'includes/functions.php';

Auth::requireLogin();

$categoryModel = new Category();
$categories = $categoryModel->getAll();

$page_title = 'Post a New Listing';
include 'includes/header.php';
?>

<main>
    <section class="form-section">
        <h2>Post a New Listing</h2>
        <?php echo form_flash(); ?>

        <form action="process_add_item.php" method="POST" enctype="multipart/form-data" id="add-item-form">
            <input type="hidden" name="csrf_token" value="<?php echo e(Auth::csrfToken()); ?>">

            <fieldset>
                <legend>Item Information</legend>

                <label for="title">Item Title</label>
                <input type="text" id="title" name="title" required maxlength="150">

                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo formatEnglishInteger($category['cat_id']); ?>"><?php echo e($category['cat_name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="price">Price (SAR)</label>
                <input type="text" id="price" name="price" required min="0.01" step="0.01" inputmode="decimal" data-numeric="decimal" pattern="[0-9]+([.][0-9]+)?" autocomplete="off">

                <label for="quantity">Quantity Available</label>
                <input type="text" id="quantity" name="quantity" required min="1" step="1" value="<?php echo formatEnglishInteger(1); ?>" inputmode="numeric" data-numeric="integer" pattern="[0-9]+" autocomplete="off">

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required></textarea>

                <label for="image">Upload Photo</label>
                <div class="custom-file-upload">
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" class="native-file-input" required>
                    <label for="image" class="file-upload-btn">Choose File</label>
                    <span class="file-upload-name" data-default="No file chosen">No file chosen</span>
                </div>
            </fieldset>

            <div class="form-message" aria-live="polite"></div>
            <button type="submit" class="submit-btn">Publish Now</button>
        </form>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
