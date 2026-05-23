<?php
function toEnglishDigits($value) {
    $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $english = ['0','1','2','3','4','5','6','7','8','9'];

    $value = str_replace($arabic, $english, (string)$value);
    $value = str_replace($persian, $english, $value);

    return $value;
}

function normalizeNumericInput($value, $allowDecimal = false)
{
    $value = toEnglishDigits((string) $value);
    $value = str_replace(['٫', '٬'], ['.', ''], $value);

    if ($allowDecimal) {
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^0-9.]/', '', $value);
        $parts = explode('.', $value);

        if (count($parts) > 1) {
            $value = array_shift($parts) . '.' . implode('', $parts);
        }

        return $value;
    }

    return preg_replace('/[^0-9]/', '', $value);
}

function normalizeNumericFields(array $data, array $fields)
{
    foreach ($fields as $field => $allowDecimal) {
        if (array_key_exists($field, $data)) {
            $data[$field] = normalizeNumericInput($data[$field], (bool) $allowDecimal);
        }
    }

    return $data;
}

function formatEnglishNumber($value, $decimals = 0)
{
    return toEnglishDigits(number_format((float) $value, $decimals));
}

function formatEnglishInteger($value)
{
    return toEnglishDigits((string) (int) $value);
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function item_image_src($imagePath)
{
    if (!empty($imagePath)) {
        $relativePath = 'uploads/' . $imagePath;
        $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativePath;

        if (is_file($fullPath)) {
            return $relativePath;
        }
    }

    return 'assets/img/no-image.svg';
}

function render_item_card(array $item)
{
    ob_start();
    ?>
    <article class="item-card">
        <img src="<?php echo e(item_image_src($item['image_path'] ?? '')); ?>" alt="<?php echo e($item['title'] ?? 'Marketplace item'); ?>">
        <div class="card-content">
            <span class="category-tag"><?php echo e($item['cat_name'] ?? 'General'); ?></span>
            <h3><?php echo e($item['title'] ?? 'Untitled item'); ?></h3>
            <p class="price"><?php echo formatEnglishNumber($item['price'] ?? 0, 2); ?> SAR</p>
            <a href="item_details.php?id=<?php echo formatEnglishInteger($item['item_id'] ?? 0); ?>" class="btn">View Details</a>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

function form_flash($key = 'form_error')
{
    if (empty($_SESSION[$key])) {
        return '';
    }

    $message = $_SESSION[$key];
    unset($_SESSION[$key]);

    return '<div class="alert error">' . e($message) . '</div>';
}
