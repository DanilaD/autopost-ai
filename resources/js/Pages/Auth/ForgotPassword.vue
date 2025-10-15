<script setup>
import LanguageSelector from '@/Components/LanguageSelector.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
    status: {
        type: String,
    },
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <Head :title="t('auth.forgot_password_title')" />

    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 via-gray-200 to-indigo-100 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-900 relative"
    >
        <!-- Language Selector - Top Right -->
        <div class="absolute top-4 right-4 z-10">
            <LanguageSelector />
        </div>

        <div
            class="max-w-md w-full space-y-8 p-10 bg-glass-card shadow-glass-md"
        >
            <!-- Logo/Brand -->
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                    Autopost AI
                </h1>
                <p class="mt-3 text-gray-600 dark:text-gray-400">
                    {{ t('auth.forgot_password_title') }}
                </p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('auth.forgot_password_description') }}
                </p>
            </div>

            <!-- Status Message -->
            <div
                v-if="status"
                class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-center"
            >
                <p
                    class="text-sm font-medium text-green-800 dark:text-green-200"
                >
                    {{ status }}
                </p>
            </div>

            <!-- Forgot Password Form -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div>
                    <label for="email" class="sr-only">
                        {{ t('auth.email') }}
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        class="appearance-none relative block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                        :placeholder="t('auth.email')"
                    />
                    <div
                        v-if="form.errors.email"
                        class="mt-2 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex space-x-3">
                    <button
                        type="button"
                        class="flex-1 py-3 px-4 border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        @click="$inertia.visit(route('login'))"
                    >
                        {{ t('auth.back') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 py-3 px-4 border border-transparent text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        {{ t('auth.reset') }}
                    </button>
                </div>
            </form>

            <!-- Back to Login Link -->
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ t('auth.remember_password') }}
                    <Link
                        :href="route('login')"
                        class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors"
                    >
                        {{ t('auth.back_to_login') }}
                    </Link>
                </p>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                <p>{{ t('auth.secure_auth') }}</p>
            </div>
        </div>
    </div>
</template>
