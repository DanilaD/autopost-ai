<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Search...',
    },
    routeName: {
        type: String,
        required: false,
    },
    preserveState: {
        type: Boolean,
        default: true,
    },
    debounce: {
        type: Number,
        default: 300,
    },
})

const emit = defineEmits(['update:modelValue'])

const search = ref(props.modelValue)
let timeout = null

// Watch for changes and emit with debounce
watch(search, (value) => {
    emit('update:modelValue', value)

    if (props.routeName) {
        clearTimeout(timeout)
        timeout = setTimeout(() => {
            router.get(
                route(props.routeName),
                { search: value },
                {
                    preserveState: props.preserveState,
                    preserveScroll: true,
                    replace: true,
                }
            )
        }, props.debounce)
    }
})

const clearSearch = () => {
    search.value = ''
}
</script>

<template>
    <div class="relative">
        <input
            v-model="search"
            type="text"
            :placeholder="placeholder"
            class="w-full rounded-md border-gray-300 pe-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
        />
        <div class="absolute inset-y-0 end-0 flex items-center pe-3">
            <button
                v-if="search"
                type="button"
                class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                @click="clearSearch"
            >
                <svg
                    class="h-4 w-4"
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
            <svg
                v-else
                class="h-4 w-4 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
            </svg>
        </div>
    </div>
</template>
