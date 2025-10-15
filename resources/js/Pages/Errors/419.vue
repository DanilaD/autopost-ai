<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSelector from '@/Components/LanguageSelector.vue'

const { t } = useI18n()

defineProps({
    status: {
        type: Number,
        default: 419,
    },
})
</script>

<template>
    <Head :title="t('errors.csrf_mismatch')" />

    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-pattern-neutral-100 via-pattern-neutral-200 to-pattern-primary-light dark:from-pattern-neutral-900 dark:via-pattern-neutral-800 dark:to-pattern-primary-dark relative"
    >
        <!-- Language Selector - Top Right -->
        <div class="absolute top-4 right-4 z-10">
            <LanguageSelector />
        </div>

        <div
            class="max-w-md w-full space-y-8 p-10 bg-glass-card shadow-glass-md text-center"
        >
            <!-- Error Code -->
            <div
                class="text-8xl font-bold text-purple-600 dark:text-purple-400"
            >
                {{ status }}
            </div>

            <!-- Error Title -->
            <div>
                <h1
                    class="text-3xl font-bold text-pattern-neutral-900 dark:text-pattern-neutral-100"
                >
                    {{ t('errors.csrf_mismatch') }}
                </h1>
                <p
                    class="mt-3 text-pattern-neutral-600 dark:text-pattern-neutral-400"
                >
                    {{ t('errors.csrf_mismatch_description') }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <Link
                    :href="route('login')"
                    class="w-full py-3 px-4 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 rounded-md inline-block"
                >
                    {{ t('auth.login') }}
                </Link>

                <button
                    class="w-full py-3 px-4 text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300 bg-pattern-neutral-100 dark:bg-pattern-neutral-700 hover:bg-pattern-neutral-200 dark:hover:bg-pattern-neutral-600 focus:outline-none focus:ring-2 focus:ring-pattern-primary focus:ring-offset-2 transition-all duration-200 rounded-md"
                    @click="$inertia.reload()"
                >
                    {{ t('errors.refresh_page') }}
                </button>
            </div>

            <!-- Footer -->
            <div
                class="text-center text-sm text-pattern-neutral-500 dark:text-pattern-neutral-400"
            >
                <p>{{ t('errors.need_help') }}</p>
            </div>
        </div>
    </div>
</template>
