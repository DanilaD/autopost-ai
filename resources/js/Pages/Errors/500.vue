<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSelector from '@/Components/LanguageSelector.vue'

const { t } = useI18n()

defineProps({
    status: {
        type: Number,
        default: 500,
    },
})
</script>

<template>
    <Head :title="t('errors.server_error')" />

    <div
        class="min-h-screen flex items-center justify-center bg-md-background relative"
    >
        <!-- Language Selector - Top Right -->
        <div class="absolute top-4 right-4 z-10">
            <LanguageSelector />
        </div>

        <div
            class="max-w-md w-full space-y-8 p-10 bg-md-surface-container shadow-elevation-3 rounded-xl text-center"
        >
            <!-- Error Code -->
            <div class="text-8xl font-bold text-md-error">
                {{ status }}
            </div>

            <!-- Error Title -->
            <div>
                <h1 class="text-3xl font-bold text-md-on-surface">
                    {{ t('errors.server_error') }}
                </h1>
                <p class="mt-3 text-md-on-surface-variant">
                    {{ t('errors.server_error_description') }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <Link
                    :href="route('dashboard')"
                    class="w-full py-3 px-4 text-sm font-medium text-md-on-primary bg-md-primary hover:bg-md-primary-container hover:text-md-on-primary-container focus:outline-none focus:ring-2 focus:ring-md-primary focus:ring-offset-2 transition-all duration-medium2 rounded-md inline-block"
                >
                    {{ t('errors.go_home') }}
                </Link>

                <button
                    class="w-full py-3 px-4 text-sm font-medium text-md-on-surface bg-md-surface-container-high hover:bg-md-surface-container focus:outline-none focus:ring-2 focus:ring-md-outline focus:ring-offset-2 transition-all duration-medium2 rounded-md"
                    @click="$inertia.reload()"
                >
                    {{ t('errors.try_again') }}
                </button>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-md-on-surface-variant">
                <p>{{ t('errors.contact_support') }}</p>
            </div>
        </div>
    </div>
</template>
