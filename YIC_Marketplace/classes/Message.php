<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Listing.php';

class Message
{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    public function sendInquiry($itemId, $senderId, $messageText)
    {
        $messageText = trim($messageText);
        if ($messageText === '') {
            throw new InvalidArgumentException('Message cannot be empty.');
        }

        $listing = new Listing($this->pdo);
        $item = $listing->findById($itemId);
        if (!$item) {
            throw new InvalidArgumentException('Item not found.');
        }

        if ((int) $item['seller_id'] === (int) $senderId) {
            throw new InvalidArgumentException('You cannot send an inquiry to yourself.');
        }

        $stmt = $this->pdo->prepare('INSERT INTO inquiries (item_id, sender_id, receiver_id, message_text) VALUES (?, ?, ?, ?)');
        $stmt->execute([(int) $itemId, (int) $senderId, (int) $item['seller_id'], $messageText]);

        return (int) $this->pdo->lastInsertId();
    }

    public function inboxForUser($userId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.*, u.name AS sender_name, i.title AS item_title
             FROM inquiries m
             INNER JOIN users u ON m.sender_id = u.user_id
             INNER JOIN items i ON m.item_id = i.item_id
             WHERE m.receiver_id = ?
             ORDER BY m.sent_at DESC'
        );
        $stmt->execute([(int) $userId]);
        return $stmt->fetchAll();
    }
}
