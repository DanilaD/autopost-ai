<?php

namespace App\Enums;

/**
 * Instagram Account Permission Levels
 *
 * Defines what actions a user can perform on a shared Instagram account.
 * Used in the instagram_account_user pivot table.
 */
enum InstagramAccountPermission: string
{
    /**
     * Can only view account information
     */
    case VIEW = 'view';

    /**
     * Can create and publish posts to the account
     */
    case POST = 'post';

    /**
     * Full management: connect, disconnect, modify settings, manage sharing
     */
    case MANAGE = 'manage';

    /**
     * Get a human-readable label for the permission
     */
    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Only',
            self::POST => 'Can Post',
            self::MANAGE => 'Full Management',
        };
    }

    /**
     * Get a description of what this permission allows
     */
    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view account details and analytics',
            self::POST => 'Can create and publish posts to this account',
            self::MANAGE => 'Can modify account settings, reconnect, and manage sharing permissions',
        };
    }

    /**
     * Check if this permission includes posting rights
     */
    public function canPost(): bool
    {
        return in_array($this, [self::POST, self::MANAGE]);
    }

    /**
     * Check if this permission includes management rights
     */
    public function canManage(): bool
    {
        return $this === self::MANAGE;
    }
}
