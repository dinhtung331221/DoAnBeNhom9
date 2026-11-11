<?php

namespace App\Core;

class Auth
{
    public static function user()
    {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }

    public static function id()
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check()
    {
        return self::user() !== null;
    }

    public static function attempt($username, $password)
    {
        $statement = Database::connection()->prepare('SELECT id, username, password_hash, ho_ten, email, role, trang_thai FROM users WHERE username = :username LIMIT 1');
        $statement->execute(array('username' => $username));
        $user = $statement->fetch();
        if (!$user || !(int) $user['trang_thai'] || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        unset($user['password_hash']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        return true;
    }

    public static function logout()
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function hasRole($roles)
    {
        $user = self::user();
        return $user && in_array($user['role'], (array) $roles, true);
    }

    public static function requireRole($roles)
    {
        if (!self::check()) {
            header('Location: ' . url('login'));
            exit;
        }
        if (!self::hasRole($roles)) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            exit;
        }
    }
}

