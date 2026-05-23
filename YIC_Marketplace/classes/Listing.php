<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Category.php';
require_once __DIR__ . '/../includes/functions.php';

class Listing
{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    public function getAvailable($search = '', $categoryId = 0)
    {
        $sql = "SELECT i.*, c.cat_name
                FROM items i
                INNER JOIN categories c ON i.cat_id = c.cat_id
                WHERE i.status = 'available'";
        $params = [];

        $search = trim($search);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (i.title LIKE ? OR i.description LIKE ? OR c.cat_name LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $categoryId = (int) $categoryId;
        if ($categoryId > 0) {
            $sql .= ' AND i.cat_id = ?';
            $params[] = $categoryId;
        }

        $sql .= ' ORDER BY i.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($itemId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM items WHERE item_id = ? LIMIT 1');
        $stmt->execute([(int) $itemId]);
        return $stmt->fetch();
    }

    public function findWithSeller($itemId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, c.cat_name, u.name AS seller_name
             FROM items i
             INNER JOIN categories c ON i.cat_id = c.cat_id
             INNER JOIN users u ON i.seller_id = u.user_id
             WHERE i.item_id = ?
             LIMIT 1'
        );
        $stmt->execute([(int) $itemId]);
        return $stmt->fetch();
    }

    public function getBySeller($sellerId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM items WHERE seller_id = ? ORDER BY created_at DESC');
        $stmt->execute([(int) $sellerId]);
        return $stmt->fetchAll();
    }

    public function getAllWithSellers()
    {
        $stmt = $this->pdo->query(
            'SELECT i.*, u.name AS seller_name
             FROM items i
             LEFT JOIN users u ON i.seller_id = u.user_id
             ORDER BY i.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function getTopStock($limit = 5)
    {
        $limit = max(1, min(20, (int) $limit));
        $stmt = $this->pdo->query("SELECT title, price, quantity FROM items ORDER BY quantity DESC LIMIT $limit");
        return $stmt->fetchAll();
    }

    public function create($sellerId, array $data, $imagePath)
    {
        $data = $this->validateListingData($data, true);

        $stmt = $this->pdo->prepare(
            "INSERT INTO items (seller_id, cat_id, title, description, price, quantity, image_path, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'available')"
        );
        $stmt->execute([
            (int) $sellerId,
            $data['cat_id'],
            $data['title'],
            $data['description'],
            $data['price'],
            $data['quantity'],
            $imagePath,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update($itemId, $currentUserId, $currentRole, array $data, $newImagePath = null)
    {
        $item = $this->findById($itemId);
        if (!$item || !$this->canManage($item, $currentUserId, $currentRole)) {
            throw new RuntimeException('Access denied: you cannot update this item.');
        }

        $data = $this->validateListingData($data, false);
        $imagePath = $newImagePath ?: $item['image_path'];

        if ($currentRole === 'admin') {
            $stmt = $this->pdo->prepare('UPDATE items SET title = ?, description = ?, price = ?, quantity = ?, cat_id = ?, image_path = ? WHERE item_id = ?');
            $stmt->execute([$data['title'], $data['description'], $data['price'], $data['quantity'], $data['cat_id'], $imagePath, (int) $itemId]);
        } else {
            $stmt = $this->pdo->prepare('UPDATE items SET title = ?, description = ?, price = ?, quantity = ?, cat_id = ?, image_path = ? WHERE item_id = ? AND seller_id = ?');
            $stmt->execute([$data['title'], $data['description'], $data['price'], $data['quantity'], $data['cat_id'], $imagePath, (int) $itemId, (int) $currentUserId]);
        }

        return true;
    }

    public function delete($itemId, $currentUserId, $currentRole)
    {
        $item = $this->findById($itemId);
        if (!$item || !$this->canManage($item, $currentUserId, $currentRole)) {
            throw new RuntimeException('Access denied: you cannot delete this item.');
        }

        if ($currentRole === 'admin') {
            $stmt = $this->pdo->prepare('DELETE FROM items WHERE item_id = ?');
            $stmt->execute([(int) $itemId]);
        } else {
            $stmt = $this->pdo->prepare('DELETE FROM items WHERE item_id = ? AND seller_id = ?');
            $stmt->execute([(int) $itemId, (int) $currentUserId]);
        }

        return $stmt->rowCount() > 0;
    }

    public function markSold($itemId, $sellerId)
    {
        $stmt = $this->pdo->prepare("UPDATE items SET status = 'sold' WHERE item_id = ? AND seller_id = ?");
        $stmt->execute([(int) $itemId, (int) $sellerId]);
        return $stmt->rowCount() > 0;
    }

    public function canManage(array $item, $currentUserId, $currentRole)
    {
        return $currentRole === 'admin' || (int) $item['seller_id'] === (int) $currentUserId;
    }

    public function saveUploadedImage(array $file = null, $required = false)
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                throw new InvalidArgumentException('Please upload an image for the listing.');
            }

            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Image upload failed. Please try again.');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('Image size must be 2 MB or less.');
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($file['tmp_name']);
        }

        if (!isset($allowed[$mime])) {
            throw new InvalidArgumentException('Only JPG, PNG, GIF, and WEBP images are allowed.');
        }

        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
        $baseName = trim($baseName, '-_');
        if ($baseName === '') {
            $baseName = 'item';
        }

        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $baseName . '.' . $allowed[$mime];
        $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $target = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException('Could not save uploaded image.');
        }

        return $fileName;
    }

    private function validateListingData(array $data, $newListing)
    {
        $data = normalizeNumericFields($data, [
            'price' => true,
            'quantity' => false,
            'category' => false,
        ]);

        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_FLOAT);
        $quantity = filter_var($data['quantity'] ?? null, FILTER_VALIDATE_INT);
        $catId = filter_var($data['category'] ?? null, FILTER_VALIDATE_INT);

        if ($title === '' || strlen($title) > 150) {
            throw new InvalidArgumentException('Please enter a listing title up to 150 characters.');
        }

        if ($price === false || $price <= 0) {
            throw new InvalidArgumentException('Price must be greater than zero.');
        }

        $minimumQuantity = $newListing ? 1 : 0;
        if ($quantity === false || $quantity < $minimumQuantity) {
            throw new InvalidArgumentException('Please enter a valid quantity.');
        }

        $category = new Category($this->pdo);
        if ($catId === false || !$category->exists($catId)) {
            throw new InvalidArgumentException('Please choose a valid category.');
        }

        return [
            'title' => $title,
            'description' => $description,
            'price' => (float) $price,
            'quantity' => (int) $quantity,
            'cat_id' => (int) $catId,
        ];
    }
}
