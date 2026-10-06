<?php
// File: /app/Core/Auth.php

namespace NexaT\Core;

class Auth
{
    private $session;
    private $db;
    private $user = null;
    private $userModel = 'NexaT\\Models\\User';

    private ?string $lastError = null;

    public function __construct()
    {
        if (class_exists(Session::class)) {
            $this->session = new Session();
        } elseif (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $this->db = Database::getInstance();

        if ($this->hasTimedOut()) {
            $this->forceLogout('timeout');
            return;
        }

        $this->touchActivity();

        $this->loadUser();
    }

    private function loadUser(): void
    {
        $sessionKey = defined('SESSION_USER_KEY') ? \SESSION_USER_KEY : 'user_id';
        $userId = $_SESSION[$sessionKey] ?? null;

        if (!$userId) {
            return;
        }

        if (!class_exists($this->userModel)) {
            return;
        }

        $user = $this->userModel::findWithoutScope((int)$userId)
            ?? $this->userModel::find((int)$userId);

        if ($user) {
            $this->user = $user;
        }
    }

    public function attempt(string $identifier, string $password): bool
    {
        $this->lastError = null;
        $identifier = trim($identifier);

        if ($identifier === '' || $password === '') {
            $this->lastError = 'Please enter your email/username and password.';
            return false;
        }

        if (!class_exists($this->userModel)) {
            $this->lastError = 'User model not available.';
            return false;
        }

        $user = $this->userModel::findByEmailOrUsernameGlobal($identifier);

        if (!$user) {
            $this->lastError = 'Incorrect email/username or password.';
            return false;
        }

        if (!password_verify($password, (string)$user->password)) {
            $this->lastError = 'Incorrect email/username or password.';
            return false;
        }

        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            if (method_exists($user, 'save')) {
                $user->save();
            }
        }

        $status = strtolower((string)($user->status ?? 'active'));
        if ($status !== 'active') {
            $this->lastError = 'Your account is not active. Please contact your administrator.';
            return false;
        }

        $this->login($user);
        return true;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function login($user): void
    {
        $_SESSION['school_id'] = $user->school_id;
        $_SESSION['user_id']   = $user->id;
        $_SESSION['username']  = $user->username;

        if ($this->session) {
            $this->session->regenerate();
            $this->session->set(SESSION_USER_KEY, $user->id);
        } else {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
        }

        $_SESSION[SESSION_USER_KEY] = $user->id;

        $_SESSION['_login_time']    = time();
        $_SESSION['_last_activity'] = time();

        if (isset($user->last_login_at) && method_exists($user, 'save')) {
            $user->last_login_at = date('Y-m-d H:i:s');
            $user->save();
        }

        $this->user = $user;
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->user = null;
            return;
        }

        $sessionKey = defined('SESSION_USER_KEY') ? \SESSION_USER_KEY : 'user_id';

        unset(
            $_SESSION[$sessionKey],
            $_SESSION['user_id'],
            $_SESSION['username'],
            $_SESSION['school_id'],
            $_SESSION['school_name'],
            $_SESSION['_last_activity'],
            $_SESSION['_login_time'],
            $_SESSION['_just_logged_in']
        );

        session_regenerate_id(true);

        $this->user = null;
    }

    public function getUser()  { return $this->user; }
    public function user()     { return $this->getUser(); }
    public function id()       { return $this->user ? $this->user->id : null; }

    public function check(): bool
    {
        if ($this->user === null) {
            $this->loadUser();
        }
        return $this->user !== null;
    }

    public function reloadUser()
    {
        if ($this->user) {
            if (method_exists($this->user, 'reloadPermissions')) {
                $this->user->reloadPermissions();
            }
            $this->loadUser();
        }
        return $this;
    }

    private function hasTimedOut(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $sessionKey = defined('SESSION_USER_KEY') ? \SESSION_USER_KEY : 'user_id';

        if (empty($_SESSION[$sessionKey]) && empty($_SESSION['user_id'])) {
            return false;
        }

        $idleWindow     = defined('SESSION_IDLE_TIMEOUT')     ? SESSION_IDLE_TIMEOUT     : 900;
        $absoluteWindow = defined('SESSION_ABSOLUTE_TIMEOUT') ? SESSION_ABSOLUTE_TIMEOUT : 0;

        $now          = time();
        $lastActivity = $this->session ? $this->session->lastActivity() : (int)($_SESSION['_last_activity'] ?? 0);
        $loginTime    = $this->session ? $this->session->loginTime()    : (int)($_SESSION['_login_time']    ?? 0);

        if ($lastActivity > 0 && ($now - $lastActivity) > $idleWindow) {
            return true;
        }

        if ($absoluteWindow > 0 && $loginTime > 0 && ($now - $loginTime) > $absoluteWindow) {
            return true;
        }

        return false;
    }

    private function touchActivity(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        if ($this->session) {
            $this->session->touchActivity();
        } else {
            $_SESSION['_last_activity'] = time();
        }
    }

    private function forceLogout(string $reason = 'timeout'): void
    {
        $_SESSION['_logout_reason'] = $reason;

        $sessionKey = defined('SESSION_USER_KEY') ? \SESSION_USER_KEY : 'user_id';
        unset(
            $_SESSION[$sessionKey],
            $_SESSION['user_id'],
            $_SESSION['username'],
            $_SESSION['school_id'],
            $_SESSION['school_name'],
            $_SESSION['_last_activity'],
            $_SESSION['_login_time']
        );

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $this->user = null;
    }

    public static function pullLogoutReason(): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        $reason = $_SESSION['_logout_reason'] ?? null;
        unset($_SESSION['_logout_reason']);

        return $reason;
    }
}