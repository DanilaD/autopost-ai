<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
    type: {
        type: String,
        default: 'success', // success, error, warning, info
    },
    message: {
        type: String,
        required: true,
    },
    duration: {
        type: Number,
        default: 3000,
    },
})

const emit = defineEmits(['close'])

const show = ref(false)

const borderColor = computed(() => {
    const colors = {
        success: 'border-l-success-500',
        error: 'border-l-error-500',
        warning: 'border-l-warning-500',
        info: 'border-l-primary-500',
    }
    return colors[props.type] || colors.success
})

const iconColor = computed(() => {
    const colors = {
        success: 'text-success-500',
        error: 'text-error-500',
        warning: 'text-warning-500',
        info: 'text-primary-500',
    }
    return colors[props.type] || colors.success
})

const icon = computed(() => {
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ',
    }
    return icons[props.type] || icons.success
})

onMounted(() => {
    // Slide in animation
    setTimeout(() => {
        show.value = true
    }, 10)

    // Auto close after duration
    if (props.duration > 0) {
        setTimeout(() => {
            close()
        }, props.duration)
    }
})

const close = () => {
    show.value = false
    setTimeout(() => {
        emit('close')
    }, 300) // Wait for animation to complete
}
</script>

<template>
    <div
        :class="[
            'transform transition-all duration-300 ease-out',
            show ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0',
        ]"
    >
        <div
            :class="[
                borderColor,
                'bg-white dark:bg-gray-800 border-l-4 rounded-lg shadow-lg px-4 py-3 text-gray-900 dark:text-gray-100 flex items-center space-x-2 min-w-[250px] max-w-sm',
            ]"
        >
            <!-- Icon -->
            <div class="flex-shrink-0">
                <span :class="[iconColor, 'text-lg font-bold']">{{
                    icon
                }}</span>
            </div>

            <!-- Message -->
            <div class="flex-1">
                <p class="text-sm font-medium">
                    {{ message }}
                </p>
            </div>

            <!-- Close Button -->
            <button
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                @click="close"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>
