<template>
    <div class="relative">
        <select
            :value="currentProvider"
            class="appearance-none bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 pr-8 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            @change="updateProvider"
        >
            <option
                v-for="provider in availableProviders"
                :key="provider.name"
                :value="provider.name"
                :disabled="!provider.available"
            >
                {{ provider.label }}
            </option>
        </select>

        <!-- Custom dropdown arrow -->
        <div
            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"
        >
            <ChevronDownIcon class="w-4 h-4 text-gray-400" />
        </div>
    </div>
</template>

<script setup>
import { ChevronDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    currentProvider: {
        type: String,
        default: 'local',
    },
})

const emit = defineEmits(['provider-change'])

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

const updateProvider = (event) => {
    emit('provider-change', event.target.value)
}
</script>
