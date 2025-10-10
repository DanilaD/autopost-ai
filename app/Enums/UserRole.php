<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case NETWORK = 'network';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'User',
            self::NETWORK => 'Network Manager',
        };
    }

    /**
     * Get role description
     */
    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full access to company settings and user management',
            self::USER => 'Can create and manage posts',
            self::NETWORK => 'Can manage Instagram accounts and connections',
        };
    }

    /**
     * Get all role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if role has admin privileges
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if role can manage users
     */
    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if role can manage posts
     */
    public function canManagePosts(): bool
    {
        return in_array($this, [self::ADMIN, self::USER]);
    }

    /**
     * Check if role can manage network
     */
    public function canManageNetwork(): bool
    {
        return in_array($this, [self::ADMIN, self::NETWORK]);
    }
}
