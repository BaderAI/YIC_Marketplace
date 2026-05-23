<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../includes/functions.php';

class User
{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    public function register($name, $email, $studentId, $password, $confirmPassword)
    {
        $name = trim($name);
        $email = strtolower(trim($email));
        $studentId = trim(toEnglishDigits($studentId));

        if ($name === '' || strlen($name) > 100) {
            throw new InvalidArgumentException('Please enter a valid full name.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@(student\.)?yic\.edu\.sa$/i', $email)) {
            throw new InvalidArgumentException('Please use a valid YIC college email.');
        }

        if ($studentId === '' || strlen($studentId) > 50) {
            throw new InvalidArgumentException('Please enter a valid student ID.');
        }

        if (strlen($password) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }

        if ($password !== $confirmPassword) {
            throw new InvalidArgumentException('Passwords do not match.');
        }

        if ($this->findByEmail($email)) {
            throw new InvalidArgumentException('This email is already registered.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, student_id, password_hash, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $studentId, $hash, 'student']);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([trim($email)]);
        return $stmt->fetch();
    }

    public function findById($userId)
    {
        $stmt = $this->pdo->prepare('SELECT user_id, name, email, student_id, role, created_at FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([(int) $userId]);
        return $stmt->fetch();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query('SELECT user_id, name, email, student_id, role, created_at FROM users ORDER BY user_id DESC');
        return $stmt->fetchAll();
    }

    public function deleteNonAdmin($userId, $currentAdminId)
    {
        $userId = (int) $userId;
        $currentAdminId = (int) $currentAdminId;

        if ($userId === $currentAdminId) {
            throw new InvalidArgumentException('You cannot delete your own account.');
        }

        $user = $this->findById($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found.');
        }

        if ($user['role'] === 'admin') {
            throw new InvalidArgumentException('Admin accounts are protected.');
        }

        $stmt = $this->pdo->prepare('DELETE FROM users WHERE user_id = ? AND role <> ?');
        $stmt->execute([$userId, 'admin']);
        return $stmt->rowCount() > 0;
    }
}
