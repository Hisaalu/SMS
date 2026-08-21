<?php
// File: /app/Services/AuthorizationService.php

namespace NexaT\Services;

use NexaT\Models\User;
use NexaT\Models\Role;
use NexaT\Models\Permission;

class AuthorizationService
{
    private $user;
    
    public function __construct(User $user = null)
    {
        $this->user = $user;
    }
    
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }
    
    public function can($permissionSlug)
    {
        if (!$this->user) {
            return false;
        }
        return $this->user->hasPermission($permissionSlug);
    }
    
    public function hasRole($roleSlug)
    {
        if (!$this->user) {
            return false;
        }
        return $this->user->hasRole($roleSlug);
    }
    
    public function getUserPermissions()
    {
        if (!$this->user) {
            return [];
        }
        return $this->user->permissions();
    }
    
    public function getUserRoles()
    {
        if (!$this->user) {
            return [];
        }
        return $this->user->roles();
    }
    
    public function isSuperAdmin()
    {
        if (!$this->user) {
            return false;
        }
        return $this->user->isSuperAdmin();
    }
    
    public function canManageUsers()
    {
        return $this->can('users.manage') || $this->isSuperAdmin();
    }
    
    public function canManageRoles()
    {
        return $this->can('roles.manage') || $this->isSuperAdmin();
    }
    
    public function canManageSettings()
    {
        return $this->can('settings.manage') || $this->isSuperAdmin();
    }
}