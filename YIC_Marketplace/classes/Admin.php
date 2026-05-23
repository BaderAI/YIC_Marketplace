<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Listing.php';

class Admin
{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    public function dashboardStats()
    {
        return [
            'count_users' => (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'count_items' => (int) $this->pdo->query('SELECT COUNT(*) FROM items')->fetchColumn(),
            'total_value' => (float) ($this->pdo->query('SELECT SUM(price * quantity) FROM items')->fetchColumn() ?: 0),
            'total_stock' => (int) ($this->pdo->query('SELECT SUM(quantity) FROM items')->fetchColumn() ?: 0),
        ];
    }

    public function topStockItems($limit = 5)
    {
        $listing = new Listing($this->pdo);
        return $listing->getTopStock($limit);
    }

    public function users()
    {
        $user = new User($this->pdo);
        return $user->getAll();
    }

    public function listings()
    {
        $listing = new Listing($this->pdo);
        return $listing->getAllWithSellers();
    }
}
