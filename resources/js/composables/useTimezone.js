/**
 * Composable for timezone detection and handling.
 *
 * This composable provides utilities for:
 * - Detecting user's browser timezone
 * - Getting formatted timezone information
 * - Handling timezone selection
 */

/**
 * Get the user's browser timezone using the Intl API.
 *
 * @returns {string} The detected timezone (e.g., "Europe/London") or "UTC" as fallback
 */
export function detectBrowserTimezone() {
    try {
        // Use Intl API to get the browser's timezone
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone

        // Validate that we got a proper timezone string
        if (timezone && typeof timezone === 'string' && timezone.length > 0) {
            return timezone
        }
    } catch (error) {
        console.warn('Could not detect browser timezone:', error)
    }

    // Fallback to UTC if detection fails
    return 'UTC'
}

/**
 * Get timezone offset in hours for a given timezone.
 *
 * @param {string} timezone - The timezone identifier (e.g., "Europe/London")
 * @returns {number} The offset in hours
 */
export function getTimezoneOffset(timezone) {
    try {
        const now = new Date()

        // Get offset in minutes for UTC
        const utcOffset = now.getTimezoneOffset()

        // Get offset for the specified timezone
        const tzDate = new Date(
            now.toLocaleString('en-US', { timeZone: timezone })
        )
        const utcDate = new Date(
            now.toLocaleString('en-US', { timeZone: 'UTC' })
        )

        const offsetMinutes = (tzDate - utcDate) / (1000 * 60)

        return offsetMinutes / 60
    } catch (error) {
        console.warn('Could not get timezone offset:', error)
        return 0
    }
}

/**
 * Format a date/time in the user's timezone.
 *
 * @param {Date|string} date - The date to format
 * @param {string} timezone - The timezone to use for formatting
 * @param {Intl.DateTimeFormatOptions} options - Formatting options
 * @returns {string} Formatted date string
 */
export function formatInTimezone(date, timezone, options = {}) {
    try {
        // Normalize common Laravel-like naive timestamps ("YYYY-MM-DD HH:MM:SS") to UTC
        // so that formatting with a target timeZone is correct and not double-shifted.
        let dateObj
        if (date instanceof Date) {
            dateObj = date
        } else if (typeof date === 'string') {
            const naiveMatch =
                /^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2}:\d{2})$/.exec(date)
            if (naiveMatch) {
                // Treat as UTC by appending Z and using ISO T separator
                dateObj = new Date(`${naiveMatch[1]}T${naiveMatch[2]}Z`)
            } else {
                // Fallback to native parsing
                dateObj = new Date(date)
            }
        } else {
            dateObj = new Date(date)
        }

        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: timezone,
            ...options,
        }

        return dateObj.toLocaleString(undefined, defaultOptions)
    } catch (error) {
        console.warn('Could not format date in timezone:', error)
        return String(date)
    }
}

/**
 * Check if a timezone is valid.
 *
 * @param {string} timezone - The timezone to validate
 * @returns {boolean} True if valid, false otherwise
 */
export function isValidTimezone(timezone) {
    try {
        Intl.DateTimeFormat(undefined, { timeZone: timezone })
        return true
    } catch (error) {
        return false
    }
}

export default {
    detectBrowserTimezone,
    getTimezoneOffset,
    formatInTimezone,
    isValidTimezone,
}
