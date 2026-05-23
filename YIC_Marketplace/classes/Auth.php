<?php
require_once __DIR__ . '/Database.php';

class Auth
{
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function csrfToken()
    {
        self::startSession();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf($token)
    {
        self::startSession();
        return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireValidCsrf($token)
    {
        if (!self::validateCsrf($token)) {
            http_response_code(403);
            die('Security Error: Invalid or missing CSRF token.');
        }
    }

    public static function login($email, $password)
    {
        self::startSession();

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        return true;
    }

    public static function logout()
    {
        self::startSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public static function requireLogin()
    {
        self::startSession();

        if (empty($_SESSION['user_id'])) {
            header('Location: login.php?error=access_denied');
            exit();
        }
    }

    public static function requireAdmin()
    {
        self::startSession();

        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? 'student') !== 'admin') {
            http_response_code(403);
            die("<h2 class='access-denied'>Access Denied: Admin privileges required.</h2>");
        }
    }

    public static function userId()
    {
        self::startSession();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function role()
    {
        self::startSession();
        return $_SESSION['role'] ?? 'guest';
    }

    public static function isAdmin()
    {
        return self::role() === 'admin';
    }
}
