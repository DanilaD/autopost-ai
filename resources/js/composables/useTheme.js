import { ref, watch, onMounted } from 'vue'

/**
 * Theme Management Composable
 *
 * Handles dark/light mode switching with:
 * - Persistent storage in localStorage
 * - System preference detection
 * - Reactive state management
 * - Smooth transitions
 *
 * @returns {Object} Theme state and controls
 *
 * @example
 * const { isDark, theme, toggleTheme, setTheme } = useTheme()
 */

// Check if we're in a browser environment
const isBrowser = typeof window !== 'undefined'

// Get initial theme value immediately (not in onMounted)
const getInitialTheme = () => {
    if (!isBrowser) return 'light'
    
    const stored = localStorage.getItem('theme')
    if (stored && (stored === 'dark' || stored === 'light')) {
        return stored
    }
    
    // Check system preference
    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light'
}

// Initialize state with correct values immediately
const initialTheme = getInitialTheme()
const isDark = ref(initialTheme === 'dark')
const theme = ref(initialTheme)

/**
 * Get the user's system theme preference
 * @returns {string} 'dark' or 'light'
 */
const getSystemTheme = () => {
    if (!isBrowser) return 'light'

    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light'
}

/**
 * Get stored theme from localStorage or fall back to system preference
 * @returns {string} 'dark' or 'light'
 */
const getStoredTheme = () => {
    if (!isBrowser) return 'light'

    const stored = localStorage.getItem('theme')

    // If no stored preference, use system preference
    if (!stored) {
        return getSystemTheme()
    }

    return stored === 'dark' ? 'dark' : 'light'
}

/**
 * Apply theme to the document
 * @param {string} newTheme - 'dark' or 'light'
 */
const applyTheme = (newTheme) => {
    if (!isBrowser) return

    const html = document.documentElement

    if (newTheme === 'dark') {
        html.classList.add('dark')
    } else {
        html.classList.remove('dark')
    }

    // Store preference
    localStorage.setItem('theme', newTheme)

    // Update reactive state
    theme.value = newTheme
    isDark.value = newTheme === 'dark'
}

/**
 * Set specific theme
 * @param {string} newTheme - 'dark' or 'light'
 */
const setTheme = (newTheme) => {
    applyTheme(newTheme)
}

/**
 * Toggle between dark and light themes
 */
const toggleTheme = () => {
    const newTheme = theme.value === 'dark' ? 'light' : 'dark'
    applyTheme(newTheme)
}

/**
 * Initialize theme on mount
 */
const initTheme = () => {
    const storedTheme = getStoredTheme()
    applyTheme(storedTheme)
}

/**
 * Listen for system theme changes
 */
const watchSystemTheme = () => {
    if (!isBrowser) return

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')

    // Only react to system changes if user hasn't set a preference
    const handleChange = (e) => {
        const hasStoredPreference = localStorage.getItem('theme')
        if (!hasStoredPreference) {
            applyTheme(e.matches ? 'dark' : 'light')
        }
    }

    // Modern browsers
    if (mediaQuery.addEventListener) {
        mediaQuery.addEventListener('change', handleChange)
        return () => mediaQuery.removeEventListener('change', handleChange)
    }
    // Legacy browsers
    else if (mediaQuery.addListener) {
        mediaQuery.addListener(handleChange)
        return () => mediaQuery.removeListener(handleChange)
    }
}

/**
 * Main composable function
 * @returns {Object} Theme controls and state
 */
export function useTheme() {
    onMounted(() => {
        initTheme()
        watchSystemTheme()
    })

    return {
        /**
         * Current theme ('dark' or 'light')
         * @type {import('vue').Ref<string>}
         */
        theme,

        /**
         * Whether dark mode is active
         * @type {import('vue').Ref<boolean>}
         */
        isDark,

        /**
         * Toggle between dark and light modes
         * @function
         */
        toggleTheme,

        /**
         * Set specific theme
         * @function
         * @param {string} newTheme - 'dark' or 'light'
         */
        setTheme,

        /**
         * Get system theme preference
         * @function
         * @returns {string} 'dark' or 'light'
         */
        getSystemTheme,
    }
}

