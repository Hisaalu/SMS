<?php
// File: /app/Core/Auth.php

namespace NexaT\Core;

class Auth
{
    private $session;
    private $db;
    private $user = null;
    private $userModel = 'NexaT\\Models\\User';
    
    public function __construct()
    {
        $this->session = new Session();
        $this->db = Database::getInstance();
        $this->loadUser();
    }
    
    private function loadUser(): void
    {
        $sessionKey = defined('SESSION_USER_KEY') ? \SESSION_USER_KEY : 'user_id';
        $userId = $_SESSION[$sessionKey] ?? null;
        
        if ($userId && class_exists($this->userModel)) {
            $user = $this->userModel::find($userId);
            if ($user) {
                $this->user = $user;
            }
        }
    }
    
    public function attempt(string $email, string $password): bool
    {
        $user = $this->userModel::firstWhere('email', $email);
        
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user->password)) {
            $this->login($user);
            return true;
        }
        
        return false;
    }
    
    public function login($user): void
    {
        $_SESSION['school_id'] = $user->school_id; 
        $_SESSION['user_id']   = $user->id;
        $_SESSION['username']  = $user->username;
        $this->session->regenerate();
        $this->session->set(SESSION_USER_KEY, $user->id);
        
        $_SESSION[SESSION_USER_KEY] = $user->id;
        
        if (isset($user->last_login_at)) {
            $user->last_login_at = date('Y-m-d H:i:s');
            $user->save();
        }
        
        $this->user = $user;
        session_write_close();
    }
    
    public function logout(): void
    {
        $this->session->destroy();
        $this->user = null;
        session_write_close();
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function check(): bool
    {
        if ($this->user === null) {
            $this->loadUser();
        }
        return $this->user !== null;
    }
    
    public function id()
    {
        return $this->user ? $this->user->id : null;
    }
    
    public function reloadUser()
    {
        if ($this->user) {
            $this->user->reloadPermissions();
            $this->loadUser();
        }
        return $this;
    }

    public function user()
    {
        return $this->getUser();
    }
}