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
            $this->lastError = 'Incorrect email/username or password!';
            return false;
        }

        if (!password_verify($password, (string)$user->password)) {
            $this->lastError = 'Incorrect email/username or password!';
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

        if (isset($user->last_login_at) && method_exists($user, 'save')) {
            $user->last_login_at = date('Y-m-d H:i:s');
            $user->save();
        }

        $this->user = $user;
    }

    public function logout(): void
    {
        if ($this->session) {
            $this->session->destroy();
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
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
}