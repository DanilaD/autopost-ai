<?php

namespace App\Services;

use DateTimeZone;

/**
 * Service for handling timezone operations.
 * 
 * This service provides utilities for:
 * - Getting list of available timezones
 * - Validating timezones
 * - Getting timezone information
 * - Converting between timezones
 */
class TimezoneService
{
    /**
     * Get all available timezones grouped by region.
     * 
     * Returns timezones organized by geographical regions (Africa, America, etc.)
     * for better UX in timezone selection dropdowns.
     * 
     * @return array<string, array<string, string>>
     */
    public function getGroupedTimezones(): array
    {
        $timezones = DateTimeZone::listIdentifiers();
        $grouped = [];

        foreach ($timezones as $timezone) {
            // Skip deprecated and special timezones
            if (str_starts_with($timezone, 'Etc/') || 
                str_starts_with($timezone, 'SystemV/') ||
                str_contains($timezone, 'posix')
            ) {
                continue;
            }

            $parts = explode('/', $timezone);
            $region = $parts[0] ?? 'Other';
            
            // Format timezone label (e.g., "Europe/London" -> "London (GMT+0)")
            $label = $this->formatTimezoneLabel($timezone);
            
            $grouped[$region][$timezone] = $label;
        }

        // Sort regions and timezones
        ksort($grouped);
        foreach ($grouped as &$regionTimezones) {
            asort($regionTimezones);
        }

        return $grouped;
    }

    /**
     * Get flat list of timezones for simple dropdown.
     * 
     * @return array<string, string> Array where key is timezone identifier and value is formatted label
     */
    public function getFlatTimezones(): array
    {
        $timezones = DateTimeZone::listIdentifiers();
        $flat = [];

        foreach ($timezones as $timezone) {
            // Skip deprecated and special timezones
            if (str_starts_with($timezone, 'Etc/') || 
                str_starts_with($timezone, 'SystemV/') ||
                str_contains($timezone, 'posix')
            ) {
                continue;
            }

            $flat[$timezone] = $this->formatTimezoneLabel($timezone);
        }

        asort($flat);

        return $flat;
    }

    /**
     * Format timezone label with offset information.
     * 
     * @param string $timezone Timezone identifier (e.g., "Europe/London")
     * @return string Formatted label (e.g., "London (GMT+0)")
     */
    private function formatTimezoneLabel(string $timezone): string
    {
        try {
            $dateTimeZone = new DateTimeZone($timezone);
            $dateTime = new \DateTime('now', $dateTimeZone);
            $offset = $dateTimeZone->getOffset($dateTime);
            
            $hours = floor(abs($offset) / 3600);
            $minutes = floor((abs($offset) % 3600) / 60);
            
            $sign = $offset >= 0 ? '+' : '-';
            $offsetString = sprintf('GMT%s%d', $sign, $hours);
            
            if ($minutes > 0) {
                $offsetString .= sprintf(':%02d', $minutes);
            }
            
            // Get display name (with custom overrides for special cases)
            $displayName = $this->getTimezoneDisplayName($timezone);
            
            return "{$displayName} ({$offsetString})";
        } catch (\Exception $e) {
            return $timezone;
        }
    }

    /**
     * Validate if a timezone is valid.
     * 
     * @param string $timezone Timezone identifier to validate
     * @return bool True if valid, false otherwise
     */
    public function isValid(string $timezone): bool
    {
        return in_array($timezone, DateTimeZone::listIdentifiers(), true);
    }

    /**
     * Get timezone offset in hours.
     * 
     * @param string $timezone Timezone identifier
     * @return int Offset in hours
     */
    public function getOffsetHours(string $timezone): int
    {
        try {
            $dateTimeZone = new DateTimeZone($timezone);
            $dateTime = new \DateTime('now', $dateTimeZone);
            $offset = $dateTimeZone->getOffset($dateTime);
            
            return (int) ($offset / 3600);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get list of common timezones for quick selection.
     * Focused on USA, Canada, and key international cities.
     * 
     * @return array<string, string>
     */
    public function getCommonTimezones(): array
    {
        $common = [
            // Universal
            'UTC',
            
            // USA - Major Cities
            'America/New_York',        // Eastern Time (New York, Washington DC, Miami)
            'America/Chicago',         // Central Time (Chicago, Houston, Dallas)
            'America/Denver',          // Mountain Time (Denver, Phoenix)
            'America/Los_Angeles',     // Pacific Time (Los Angeles, San Francisco, Seattle)
            'America/Anchorage',       // Alaska Time
            'Pacific/Honolulu',        // Hawaii Time
            
            // Canada - Major Cities
            'America/Toronto',         // Eastern Time (Toronto, Montreal, Ottawa)
            'America/Winnipeg',        // Central Time (Winnipeg)
            'America/Edmonton',        // Mountain Time (Edmonton, Calgary)
            'America/Vancouver',       // Pacific Time (Vancouver)
            'America/Halifax',         // Atlantic Time (Halifax)
            
            // Important International
            'America/Guayaquil',       // Ecuador (Quito, Guayaquil)
            'Europe/Minsk',            // Belarus (Minsk)
            'Europe/Kiev',             // Ukraine (Kiev/Kyiv)
        ];

        $formatted = [];
        foreach ($common as $timezone) {
            $formatted[$timezone] = $this->formatTimezoneLabel($timezone);
        }

        return $formatted;
    }
    
    /**
     * Get timezone display name with custom overrides.
     * Used for special cases where city name differs from timezone identifier.
     * 
     * @param string $timezone
     * @return string
     */
    public function getTimezoneDisplayName(string $timezone): string
    {
        // Custom display names for special cases
        $customNames = [
            'America/Guayaquil' => 'Quito/Guayaquil',  // Ecuador uses same timezone
            'Europe/Kiev' => 'Kyiv',                    // Modern spelling
            'America/New_York' => 'New York (Eastern Time)',
            'America/Chicago' => 'Chicago (Central Time)',
            'America/Denver' => 'Denver (Mountain Time)',
            'America/Los_Angeles' => 'Los Angeles (Pacific Time)',
            'America/Toronto' => 'Toronto (Eastern Time)',
            'America/Vancouver' => 'Vancouver (Pacific Time)',
        ];
        
        if (isset($customNames[$timezone])) {
            return $customNames[$timezone];
        }
        
        // Default: extract city name from timezone
        $parts = explode('/', $timezone);
        return str_replace('_', ' ', end($parts));
    }
}

