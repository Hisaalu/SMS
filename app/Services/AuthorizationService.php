<?php
// File: /app/Services/AuthorizationService.php

namespace NexaT\Services;

use NexaT\Models\User;

class AuthorizationService
{
    private ?User $user;

    public function __construct(?User $user = null)
    {
        $this->user = $user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function can(string $permissionSlug): bool
    {
        return $this->user?->hasPermission($permissionSlug) ?? false;
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->user?->hasRole($roleSlug) ?? false;
    }

    public function getUserPermissions(): array
    {
        return $this->user?->permissions() ?? [];
    }

    public function getUserRoles(): array
    {
        return $this->user?->roles() ?? [];
    }

    public function isSuperAdmin(): bool
    {
        return $this->user?->isSuperAdmin() ?? false;
    }

    public function canManageUsers(): bool
    {
        return $this->can('users.manage') || $this->isSuperAdmin();
    }

    public function canManageRoles(): bool
    {
        return $this->can('roles.manage') || $this->isSuperAdmin();
    }

    public function canManageSettings(): bool
    {
        return $this->can('settings.manage') || $this->isSuperAdmin();
    }
}