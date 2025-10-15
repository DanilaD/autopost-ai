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
    <Head :title="t('errors.session_expired')" />

    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-pattern-neutral-100 via-pattern-neutral-200 to-pattern-primary-light dark:from-pattern-neutral-900 dark:via-pattern-neutral-800 dark:to-pattern-primary-dark relative"
    >
        <!-- Language Selector - Top Right -->
        <div class="absolute top-4 right-4 z-10">
            <LanguageSelector />
        </div>

        <div
            class="max-w-md w-full space-y-8 p-10 bg-glass-soft shadow-glass-md text-center"
        >
            <!-- Error Code -->
            <div class="text-8xl font-bold text-pattern-warning">
                {{ status }}
            </div>

            <!-- Error Title -->
            <div>
                <h1
                    class="text-3xl font-bold text-pattern-neutral-900 dark:text-pattern-neutral-100"
                >
                    {{ t('errors.session_expired') }}
                </h1>
                <p
                    class="mt-3 text-pattern-neutral-600 dark:text-pattern-neutral-400"
                >
                    {{ t('errors.session_expired_description') }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <Link
                    :href="route('login')"
                    class="btn-glass-primary w-full py-3 px-4 text-sm font-medium transition-all duration-200 inline-block"
                >
                    {{ t('errors.login_again') }}
                </Link>

                <button
                    class="btn-glass-secondary w-full py-3 px-4 text-sm font-medium transition-all duration-200"
                    @click="$inertia.reload()"
                >
                    {{ t('errors.try_again') }}
                </button>
            </div>

            <!-- Footer -->
            <div
                class="text-center text-sm text-pattern-neutral-500 dark:text-pattern-neutral-400"
            >
                <p>{{ t('errors.session_timeout') }}</p>
            </div>
        </div>
    </div>
</template>
