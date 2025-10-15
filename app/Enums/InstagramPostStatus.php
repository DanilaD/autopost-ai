<?php

namespace App\Enums;

/**
 * Instagram Post Status
 * 
 * Represents the lifecycle of a post from creation to publication.
 */
enum InstagramPostStatus: string
{
    /**
     * Post is being created/edited
     */
    case DRAFT = 'draft';
    
    /**
     * Post is scheduled for future publishing
     */
    case SCHEDULED = 'scheduled';
    
    /**
     * Post is currently being sent to Instagram API
     */
    case PUBLISHING = 'publishing';
    
    /**
     * Post was successfully published to Instagram
     */
    case PUBLISHED = 'published';
    
    /**
     * Post publishing failed
     */
    case FAILED = 'failed';
    
    /**
     * Scheduled post was cancelled before publishing
     */
    case CANCELLED = 'cancelled';
    
    /**
     * Get a human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::PUBLISHING => 'Publishing',
            self::PUBLISHED => 'Published',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }
    
    /**
     * Get a CSS color class for status badges
     */
    public function colorClass(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::SCHEDULED => 'blue',
            self::PUBLISHING => 'yellow',
            self::PUBLISHED => 'green',
            self::FAILED => 'red',
            self::CANCELLED => 'gray',
        };
    }
    
    /**
     * Check if post can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::SCHEDULED]);
    }
    
    /**
     * Check if post can be cancelled
     */
    public function isCancellable(): bool
    {
        return $this === self::SCHEDULED;
    }
    
    /**
     * Check if post is in a final state
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::PUBLISHED, self::FAILED, self::CANCELLED]);
    }
}

