<template>
    <span
        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
        :class="costClasses"
    >
        <CurrencyDollarIcon class="w-3 h-3 mr-1" />
        {{ formattedCost }}
    </span>
</template>

<script setup>
import { computed } from 'vue'
import { CurrencyDollarIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    cost: {
        type: [Number, String],
        default: 0,
    },
})

const formattedCost = computed(() => {
    const cost = parseFloat(props.cost)
    if (cost === 0) return 'Free'
    if (cost < 0.01) return '< $0.01'
    return `$${cost.toFixed(4)}`
})

const costClasses = computed(() => {
    const cost = parseFloat(props.cost)
    if (cost === 0)
        return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200'
    if (cost < 0.01)
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200'
    return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200'
})
</script>
