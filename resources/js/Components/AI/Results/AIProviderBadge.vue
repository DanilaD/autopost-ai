<template>
    <span
        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
        :class="providerClasses"
    >
        <component :is="providerIcon" class="w-3 h-3 mr-1" />
        {{ providerLabel }}
    </span>
</template>

<script setup>
import { computed } from 'vue'
import {
    ComputerDesktopIcon,
    SparklesIcon,
    ChatBubbleLeftRightIcon,
    GlobeAltIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    provider: {
        type: String,
        default: 'local',
    },
})

const providerLabel = computed(() => {
    const labels = {
        local: 'Local',
        openai: 'OpenAI',
        anthropic: 'Anthropic',
        google: 'Google',
    }
    return labels[props.provider] || 'Unknown'
})

const providerIcon = computed(() => {
    const icons = {
        local: ComputerDesktopIcon,
        openai: SparklesIcon,
        anthropic: ChatBubbleLeftRightIcon,
        google: GlobeAltIcon,
    }
    return icons[props.provider] || SparklesIcon
})

const providerClasses = computed(() => {
    const classes = {
        local: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-200',
        openai: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200',
        anthropic:
            'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-200',
        google: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200',
    }
    return classes[props.provider] || classes.local
})
</script>
