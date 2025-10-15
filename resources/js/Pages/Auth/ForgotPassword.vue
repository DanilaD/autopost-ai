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
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-pattern-neutral-100 via-pattern-neutral-200 to-pattern-primary-light dark:from-pattern-neutral-900 dark:via-pattern-neutral-800 dark:to-pattern-primary-dark relative"
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
                <h1
                    class="text-4xl font-bold text-pattern-neutral-900 dark:text-pattern-neutral-100"
                >
                    Autopost AI
                </h1>
                <p
                    class="mt-3 text-pattern-neutral-600 dark:text-pattern-neutral-400"
                >
                    {{ t('auth.forgot_password_title') }}
                </p>
                <p
                    class="mt-2 text-sm text-pattern-neutral-500 dark:text-pattern-neutral-400"
                >
                    {{ t('auth.forgot_password_description') }}
                </p>
            </div>

            <!-- Status Message -->
            <div
                v-if="status"
                class="rounded-md bg-pattern-success-container p-4 text-center"
            >
                <p
                    class="text-sm font-medium text-pattern-on-success-container"
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
                        class="appearance-none relative block w-full px-4 py-3 border border-pattern-neutral-300 dark:border-pattern-neutral-600 placeholder-pattern-neutral-500 dark:placeholder-pattern-neutral-400 text-pattern-neutral-900 dark:text-pattern-neutral-100 bg-white dark:bg-pattern-neutral-800 focus:outline-none focus:ring-2 focus:ring-pattern-primary focus:border-transparent transition-all duration-200"
                        :placeholder="t('auth.email')"
                    />
                    <div
                        v-if="form.errors.email"
                        class="mt-2 text-sm text-pattern-error"
                    >
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex space-x-3">
                    <button
                        type="button"
                        class="flex-1 py-3 px-4 border border-pattern-neutral-300 dark:border-pattern-neutral-600 text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300 bg-pattern-neutral-100 dark:bg-pattern-neutral-700 hover:bg-pattern-neutral-200 dark:hover:bg-pattern-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pattern-primary"
                        @click="$inertia.visit(route('login'))"
                    >
                        Back
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
                <p
                    class="text-sm text-pattern-neutral-600 dark:text-pattern-neutral-400"
                >
                    {{ t('auth.remember_password') }}
                    <Link
                        :href="route('login')"
                        class="font-medium text-pattern-primary hover:text-pattern-primary-light transition-colors"
                    >
                        {{ t('auth.back_to_login') }}
                    </Link>
                </p>
            </div>

            <!-- Footer -->
            <div
                class="text-center text-sm text-pattern-neutral-500 dark:text-pattern-neutral-400"
            >
                <p>{{ t('auth.secure_auth') }}</p>
            </div>
        </div>
    </div>
</template>
