<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import LanguageSelector from '@/Components/LanguageSelector.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import ToastContainer from '@/Components/ToastContainer.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import { onMounted } from 'vue'

const page = usePage()
const toast = useToast()

// Show toast from session flash data
onMounted(() => {
    const toastData = page.props.toast
    if (toastData) {
        toast.addToast(toastData.message, toastData.type || 'success')
    }
})
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
        <!-- Toast Notifications -->
        <ToastContainer />

        <!-- Theme & Language Selector - Top Right -->
        <div class="absolute top-4 right-4 flex items-center space-x-2">
            <ThemeToggle />
            <LanguageSelector />
        </div>

        <div>
            <Link href="/">
                <ApplicationLogo
                    class="h-20 w-20 fill-current text-gray-500 dark:text-gray-400"
                />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>
    </div>
</template>
