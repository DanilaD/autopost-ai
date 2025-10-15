<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import LanguageSelector from '@/Components/LanguageSelector.vue'
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-md-background pt-6 sm:justify-center sm:pt-0"
    >
        <!-- Language Selector Only - Top Right -->
        <div class="absolute top-4 right-4 flex items-center space-x-2">
            <LanguageSelector />
        </div>

        <div>
            <Link href="/">
                <ApplicationLogo
                    class="h-20 w-20 fill-current text-md-on-surface-variant"
                />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-md-surface-container px-6 py-4 shadow-elevation-2 sm:max-w-md sm:rounded-md"
        >
            <slot />
        </div>
    </div>
</template>
