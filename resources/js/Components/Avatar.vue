<script setup>
import { computed } from 'vue'

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
    },
    showOnline: {
        type: Boolean,
        default: false,
    },
    isOnline: {
        type: Boolean,
        default: false,
    },
})

// Generate initials from name
const initials = computed(() => {
    return props.name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2)
})

// Size classes
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'h-8 w-8 text-xs',
        md: 'h-12 w-12 text-sm',
        lg: 'h-16 w-16 text-lg',
        xl: 'h-20 w-20 text-xl',
    }
    return sizes[props.size]
})

// Background color based on initials
const backgroundColor = computed(() => {
    const colors = [
        'bg-blue-500',
        'bg-green-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-yellow-500',
        'bg-red-500',
        'bg-teal-500',
    ]

    // Use first character to determine color
    const charCode = props.name.charCodeAt(0)
    const colorIndex = charCode % colors.length
    return colors[colorIndex]
})
</script>

<template>
    <div class="relative inline-block">
        <!-- Avatar Circle -->
        <div
            :class="[
                sizeClasses,
                backgroundColor,
                'rounded-full flex items-center justify-center text-white font-semibold shadow-sm',
            ]"
        >
            {{ initials }}
        </div>

        <!-- Online Status Indicator -->
        <div
            v-if="showOnline"
            :class="[
                'absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white',
                isOnline ? 'bg-green-400' : 'bg-gray-400',
            ]"
        />
    </div>
</template>
