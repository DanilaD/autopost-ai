<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    field: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    currentSort: {
        type: String,
        default: '',
    },
    currentDirection: {
        type: String,
        default: 'desc',
    },
})

const page = usePage()

const isActive = computed(() => props.currentSort === props.field)

const sortDirection = computed(() => {
    if (!isActive.value) return null
    return props.currentDirection
})

const handleSort = () => {
    const newDirection =
        isActive.value && props.currentDirection === 'asc' ? 'desc' : 'asc'

    router.get(
        route(route().current()),
        {
            ...route().params,
            sort: props.field,
            direction: newDirection,
            search: page.props.filters?.search,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}
</script>

<template>
    <th
        class="cursor-pointer select-none bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
        @click="handleSort"
    >
        <div class="flex items-center gap-2">
            <span>{{ label }}</span>
            <div class="flex flex-col">
                <svg
                    :class="[
                        'h-3 w-3',
                        isActive && sortDirection === 'asc'
                            ? 'text-indigo-600 dark:text-indigo-400'
                            : 'text-gray-400',
                    ]"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path d="M5 10l5-5 5 5H5z" />
                </svg>
                <svg
                    :class="[
                        'h-3 w-3 -mt-1',
                        isActive && sortDirection === 'desc'
                            ? 'text-indigo-600 dark:text-indigo-400'
                            : 'text-gray-400',
                    ]"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path d="M15 10l-5 5-5-5h10z" />
                </svg>
            </div>
        </div>
    </th>
</template>
