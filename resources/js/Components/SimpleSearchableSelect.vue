<template>
    <div class="relative">
        <!-- Selected Value Display -->
        <button
            type="button"
            @click="toggleDropdown"
            :disabled="disabled"
            class="relative w-full cursor-pointer rounded-md border bg-white py-2 pl-3 pr-10 text-left shadow-sm transition-all focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700"
            :class="[
                error ? 'border-red-500 dark:border-red-600' : 'border-gray-300',
                disabled ? 'cursor-not-allowed bg-gray-50 text-gray-500 dark:bg-gray-800' : 'hover:border-gray-400'
            ]"
        >
            <span class="block truncate text-gray-900 dark:text-gray-100">
                {{ selectedLabel }}
            </span>
            
            <!-- Dropdown Arrow -->
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <svg
                    class="h-5 w-5 transition-transform duration-200"
                    :class="[
                        isOpen ? 'rotate-180 transform' : '',
                        disabled ? 'text-gray-400' : 'text-gray-500 dark:text-gray-400'
                    ]"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </span>
        </button>

        <!-- Dropdown Panel -->
        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Search Input -->
                <div class="border-b border-gray-200 p-2 dark:border-gray-700">
                    <div class="relative">
                        <!-- Search Icon -->
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        
                        <input
                            ref="searchInput"
                            v-model="searchQuery"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="block w-full rounded-md border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        
                        <!-- Clear Search -->
                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Options List -->
                <div class="max-h-60 overflow-auto py-1">
                    <!-- No Results -->
                    <div
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-2 text-center text-sm text-gray-500 dark:text-gray-400"
                    >
                        No results found
                    </div>

                    <!-- Options -->
                    <button
                        v-for="option in filteredOptions"
                        :key="option.value"
                        type="button"
                        @click="selectOption(option.value)"
                        class="block w-full cursor-pointer px-3 py-2 text-left text-sm transition-colors hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                        :class="
                            option.value === modelValue
                                ? 'bg-indigo-100 text-indigo-900 dark:bg-indigo-900/50 dark:text-indigo-100'
                                : 'text-gray-900 dark:text-gray-100'
                        "
                    >
                        <div class="flex items-center justify-between">
                            <span>{{ option.label }}</span>
                            <svg
                                v-if="option.value === modelValue"
                                class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: null,
    },
    options: {
        type: Array,
        required: true,
        // Expected format: [{ value: 'UTC', label: 'UTC (GMT+0)' }, ...]
    },
    placeholder: {
        type: String,
        default: 'Select an option...',
    },
    searchPlaceholder: {
        type: String,
        default: 'Search...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref(null)
const dropdownRef = ref(null)

// Get the label for the currently selected value
const selectedLabel = computed(() => {
    if (!props.modelValue) {
        return props.placeholder
    }
    
    const selected = props.options.find(option => option.value === props.modelValue)
    return selected ? selected.label : props.placeholder
})

// Filter options based on search query
const filteredOptions = computed(() => {
    if (!searchQuery.value) {
        return props.options
    }

    const query = searchQuery.value.toLowerCase()
    return props.options.filter(option => 
        option.label.toLowerCase().includes(query) ||
        option.value.toLowerCase().includes(query)
    )
})

// Toggle dropdown
const toggleDropdown = () => {
    if (props.disabled) return
    
    isOpen.value = !isOpen.value
    
    if (isOpen.value) {
        // Focus search input when opening
        setTimeout(() => {
            searchInput.value?.focus()
        }, 50)
    } else {
        // Clear search when closing
        searchQuery.value = ''
    }
}

// Select an option
const selectOption = (value) => {
    emit('update:modelValue', value)
    isOpen.value = false
    searchQuery.value = ''
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false
        searchQuery.value = ''
    }
}

// Keyboard navigation
const handleKeydown = (event) => {
    if (event.key === 'Escape') {
        isOpen.value = false
        searchQuery.value = ''
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleKeydown)
})
</script>
