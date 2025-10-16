<script setup>
import { useTheme } from '@/composables/useTheme'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const { isDark, toggleTheme } = useTheme()
</script>

<template>
    <div class="relative">
        <button
            type="button"
            :aria-label="
                isDark ? t('theme.switchToLight') : t('theme.switchToDark')
            "
            :title="isDark ? t('theme.switchToLight') : t('theme.switchToDark')"
            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition-colors duration-200 ease-in-out hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:text-gray-100 dark:focus:ring-offset-gray-900"
            @click="toggleTheme"
        >
            <!-- Sun Icon (shown in dark mode) -->
            <svg
                v-if="isDark"
                class="h-5 w-5 transition-transform duration-300 ease-in-out hover:rotate-180"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                />
            </svg>

            <!-- Moon Icon (shown in light mode) -->
            <svg
                v-else
                class="h-5 w-5 transition-transform duration-300 ease-in-out"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                />
            </svg>
        </button>
    </div>
</template>
