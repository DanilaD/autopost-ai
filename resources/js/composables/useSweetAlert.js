import Swal from 'sweetalert2'

/**
 * Global SweetAlert configuration with smaller, fixed-width design
 */
const defaultConfig = {
    width: '400px', // Fixed width - smaller than default
    padding: '1.5rem', // Reduced padding
    customClass: {
        popup: 'dark:bg-gray-800 !w-[400px]', // Fixed width with dark mode
        title: 'dark:text-gray-100 text-lg', // Smaller title
        htmlContainer: 'dark:text-gray-300 text-sm', // Smaller text
        confirmButton: 'px-3 py-2 rounded-md text-sm', // Smaller buttons
        cancelButton: 'px-3 py-2 rounded-md text-sm',
        actions: 'gap-2', // Smaller gap between buttons
    },
    buttonsStyling: false, // Disable default styling to use custom classes
    // Using SweetAlert's built-in animations instead of animate.css
    showClass: {
        popup: 'swal2-show',
    },
    hideClass: {
        popup: 'swal2-hide',
    },
}

export function useSweetAlert() {
    const fire = (config) => {
        return Swal.fire({
            ...defaultConfig,
            ...config,
            customClass: {
                ...defaultConfig.customClass,
                ...config.customClass,
            },
        })
    }

    const confirm = (title, text, options = {}) => {
        return fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            ...options,
        })
    }

    const success = (title, text, options = {}) => {
        return fire({
            title,
            text,
            icon: 'success',
            confirmButtonColor: '#10b981',
            confirmButtonText: 'OK',
            ...options,
        })
    }

    const error = (title, text, options = {}) => {
        return fire({
            title,
            text,
            icon: 'error',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK',
            ...options,
        })
    }

    const warning = (title, text, options = {}) => {
        return fire({
            title,
            text,
            icon: 'warning',
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'OK',
            ...options,
        })
    }

    const info = (title, text, options = {}) => {
        return fire({
            title,
            text,
            icon: 'info',
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'OK',
            ...options,
        })
    }

    return {
        fire,
        confirm,
        success,
        error,
        warning,
        info,
    }
}
