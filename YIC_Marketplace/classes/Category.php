<?php
require_once __DIR__ . '/Database.php';

class Category
{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query('SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC');
        return $stmt->fetchAll();
    }

    public function exists($categoryId)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE cat_id = ?');
        $stmt->execute([(int) $categoryId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
