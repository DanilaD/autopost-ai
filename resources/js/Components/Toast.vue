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

const bgColor = computed(() => {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500',
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
            'fixed top-4 right-4 z-50 transform transition-all duration-300 ease-out',
            show ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0',
        ]"
    >
        <div
            :class="[
                bgColor,
                'rounded-lg shadow-lg px-6 py-4 text-white flex items-center space-x-3 min-w-[300px] max-w-md',
            ]"
        >
            <!-- Icon -->
            <div class="flex-shrink-0">
                <span class="text-2xl font-bold">{{ icon }}</span>
            </div>

            <!-- Message -->
            <div class="flex-1">
                <p class="font-medium">
                    {{ message }}
                </p>
            </div>

            <!-- Close Button -->
            <button
                class="flex-shrink-0 text-white hover:text-gray-200 transition-colors"
                @click="close"
            >
                <svg
                    class="w-5 h-5"
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
