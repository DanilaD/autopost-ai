import { reactive } from 'vue'

// Global toast state
const toasts = reactive([])

let nextId = 0

export function useToast() {
    const addToast = (message, type = 'success', duration = 3000) => {
        const id = nextId++

        const toast = {
            id,
            message,
            type,
            duration,
        }

        toasts.push(toast)

        return id
    }

    const removeToast = (id) => {
        const index = toasts.findIndex((t) => t.id === id)
        if (index > -1) {
            toasts.splice(index, 1)
        }
    }

    const success = (message, duration = 3000) => {
        return addToast(message, 'success', duration)
    }

    const error = (message, duration = 4000) => {
        return addToast(message, 'error', duration)
    }

    const warning = (message, duration = 3500) => {
        return addToast(message, 'warning', duration)
    }

    const info = (message, duration = 3000) => {
        return addToast(message, 'info', duration)
    }

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        warning,
        info,
    }
}
