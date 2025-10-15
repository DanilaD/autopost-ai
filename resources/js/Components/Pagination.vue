<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: {
        type: Array,
        required: true,
    },
})
</script>

<template>
    <div
        v-if="links.length > 3"
        class="flex flex-wrap items-center justify-center gap-1 border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
    >
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                class="inline-flex items-center rounded-md border px-3 py-2 text-sm font-medium transition-colors"
                :class="[
                    link.active
                        ? 'border-indigo-500 bg-indigo-500 text-white'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                ]"
                preserve-scroll
            >
                {{ link.label }}
            </Link>
            <span
                v-else
                class="inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm font-medium text-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600"
            >
                {{ link.label }}
            </span>
        </template>
    </div>
</template>
