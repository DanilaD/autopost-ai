<template>
    <div class="flex items-center space-x-3">
        <!-- Current Provider -->
        <div class="flex items-center space-x-2">
            <div :class="providerIconClass">
                <component :is="providerIcon" class="w-4 h-4" />
            </div>
            <span
                class="text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300"
            >
                {{ providerName }}
            </span>
            <AIStatusIndicator :status="providerStatus" />
        </div>

        <!-- Provider Dropdown -->
        <div class="relative">
            <button
                class="flex items-center text-sm text-pattern-neutral-600 dark:text-pattern-neutral-400 hover:text-pattern-neutral-800 dark:hover:text-pattern-neutral-200"
                @click="showDropdown = !showDropdown"
            >
                <ChevronDownIcon class="w-4 h-4" />
            </button>

            <!-- Dropdown Menu -->
            <div
                v-show="showDropdown"
                class="absolute right-0 mt-2 w-48 bg-glass-card rounded-md shadow-glass-lg z-10"
            >
                <div class="py-1">
                    <button
                        v-for="provider in availableProviders"
                        :key="provider.name"
                        :class="[
                            'w-full text-left px-4 py-2 text-sm',
                            provider.available
                                ? 'text-pattern-neutral-700 dark:text-pattern-neutral-300 hover:bg-glass-soft'
                                : 'text-pattern-neutral-400 dark:text-pattern-neutral-500 cursor-not-allowed',
                        ]"
                        :disabled="!provider.available"
                        @click="selectProvider(provider.name)"
                    >
                        <div class="flex items-center justify-between">
                            <span>{{ provider.label }}</span>
                            <AIStatusIndicator
                                :status="
                                    provider.available
                                        ? 'available'
                                        : 'unavailable'
                                "
                            />
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'
import AIStatusIndicator from './AIStatusIndicator.vue'

const props = defineProps({
    currentProvider: {
        type: String,
        default: 'local',
    },
})

const emit = defineEmits(['provider-change'])

const showDropdown = ref(false)

const availableProviders = [
    {
        name: 'local',
        label: 'Local AI (Free)',
        available: true,
    },
    {
        name: 'openai',
        label: 'OpenAI',
        available: false, // Will be updated based on API status
    },
    {
        name: 'anthropic',
        label: 'Anthropic',
        available: false,
    },
    {
        name: 'google',
        label: 'Google AI',
        available: false,
    },
]

const providerName = computed(() => {
    const provider = availableProviders.find(
        (p) => p.name === props.currentProvider
    )
    return provider?.label || 'Unknown'
})

const providerStatus = computed(() => {
    const provider = availableProviders.find(
        (p) => p.name === props.currentProvider
    )
    return provider?.available ? 'available' : 'unavailable'
})

const providerIcon = computed(() => {
    const icons = {
        local: 'ComputerDesktopIcon',
        openai: 'SparklesIcon',
        anthropic: 'ChatBubbleLeftRightIcon',
        google: 'GlobeAltIcon',
    }
    return icons[props.currentProvider] || 'QuestionMarkCircleIcon'
})

const providerIconClass = computed(() => {
    const classes = {
        local: 'text-pattern-neutral-600 dark:text-pattern-neutral-400',
        openai: 'text-pattern-success dark:text-pattern-success',
        anthropic: 'text-pattern-accent dark:text-pattern-accent',
        google: 'text-pattern-info dark:text-pattern-info',
    }
    return (
        classes[props.currentProvider] ||
        'text-pattern-neutral-600 dark:text-pattern-neutral-400'
    )
})

const selectProvider = (providerName) => {
    emit('provider-change', providerName)
    showDropdown.value = false
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        showDropdown.value = false
    }
}

// Add event listener for outside clicks
onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>
