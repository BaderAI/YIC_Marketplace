<?php
require_once 'classes/Listing.php';
require_once 'includes/functions.php';

header('Content-Type: text/html; charset=UTF-8');

$search = trim($_GET['q'] ?? '');
$category = (int) normalizeNumericInput($_GET['category'] ?? 0);

try {
    $listing = new Listing();
    $items = $listing->getAvailable($search, $category);

    if (!$items) {
        echo '<p class="empty-grid-message">No items found.</p>';
        exit();
    }

    foreach ($items as $item) {
        echo render_item_card($item);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo '<p class="empty-grid-message">Search failed. Please try again.</p>';
}
