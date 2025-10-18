<template>
    <span
        :class="statusClasses"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    >
        <div :class="dotClasses" class="w-2 h-2 rounded-full mr-1.5" />
        {{ statusText }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: (value) =>
            ['available', 'unavailable', 'loading', 'error'].includes(value),
    },
})

const statusClasses = computed(() => {
    const classes = {
        available:
            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200',
        unavailable:
            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200',
        loading:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200',
        error: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200',
    }
    return classes[props.status] || classes.unavailable
})

const dotClasses = computed(() => {
    const classes = {
        available: 'bg-green-400',
        unavailable: 'bg-red-400',
        loading: 'bg-yellow-400 animate-pulse',
        error: 'bg-red-400',
    }
    return classes[props.status] || classes.unavailable
})

const statusText = computed(() => {
    const texts = {
        available: 'Available',
        unavailable: 'Unavailable',
        loading: 'Loading...',
        error: 'Error',
    }
    return texts[props.status] || 'Unknown'
})
</script>
