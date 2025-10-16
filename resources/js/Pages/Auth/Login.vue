<script setup>
import LanguageSelector from '@/Components/LanguageSelector.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'
const { t } = useI18n()

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
        default: '',
    },
    email: {
        type: String,
        default: '',
    },
    invitationToken: {
        type: String,
        default: '',
    },
})

const form = useForm({
    email: props.email || '',
    password: '',
    remember: false,
    invitation_token: props.invitationToken || '',
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}

// Render throttle message ("Too many attempts") as small red text
const isThrottleStatus = computed(() => {
    if (!('status' in $props) || !$props.status) return false
    try {
        const s = String($props.status).toLowerCase()
        return (
            s.includes('too many') ||
            s.includes('attempts') ||
            s.includes('throttle')
        )
    } catch (e) {
        return false
    }
})

// Detect throttle error coming from validation errors (common Laravel behavior)
const hasThrottleError = computed(() => {
    try {
        const errors = /** @type {any} */ ($page).props?.errors || {}
        const emailErr = String(errors.email || '').toLowerCase()
        const genericErr = Object.values(errors)
            .map((e) => String(e).toLowerCase())
            .join(' | ')
        return (
            emailErr.includes('too many') ||
            emailErr.includes('attempts') ||
            emailErr.includes('throttle') ||
            genericErr.includes('too many attempts')
        )
    } catch (_) {
        return false
    }
})
</script>

<template>
    <Head :title="t('auth.login')" />

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
                    {{ appName }}
                </h1>
                <p class="mt-3 text-gray-600 dark:text-gray-400">
                    {{ t('auth.welcome_back') }}
                </p>
            </div>

            <!-- Error Messages -->
            <div
                v-if="
                    $page.props.errors &&
                    Object.keys($page.props.errors).length > 0
                "
            >
                <!-- Throttle shown as small red text -->
                <p
                    v-if="hasThrottleError"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{
                        $page.props.errors.email ||
                        Object.values($page.props.errors)[0]
                    }}
                </p>
                <!-- Other validation errors in banner -->
                <div v-else class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                    <div class="text-sm text-red-800 dark:text-red-200">
                        <ul class="list-disc list-inside space-y-1">
                            <li
                                v-for="(error, field) in $page.props.errors"
                                :key="field"
                            >
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Status / Throttle Message -->
            <div v-if="status">
                <!-- If throttle-like message, render as small red inline text -->
                <p
                    v-if="isThrottleStatus"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{ status }}
                </p>
                <!-- Otherwise render as previous success/info banner -->
                <div
                    v-else
                    class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-center"
                >
                    <p
                        class="text-sm font-medium text-green-800 dark:text-green-200"
                    >
                        {{ status }}
                    </p>
                </div>
            </div>

            <!-- Login Form -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <!-- Hidden invitation token -->
                <input
                    v-if="form.invitation_token"
                    type="hidden"
                    name="invitation_token"
                    :value="form.invitation_token"
                />

                <div class="space-y-4">
                    <!-- Email Field -->
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

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="sr-only">
                            {{ t('auth.password') }}
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                            :placeholder="t('auth.password')"
                        />
                        <div
                            v-if="form.errors.password"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                            />
                            <span
                                class="ml-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ t('auth.remember_me') }}
                            </span>
                        </label>

                        <div class="text-sm">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors"
                            >
                                {{ t('auth.forgot_password') }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex space-x-3">
                    <button
                        type="button"
                        class="flex-1 py-3 px-4 border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        @click="$inertia.visit(route('home'))"
                    >
                        {{ t('auth.back') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 py-3 px-4 border border-transparent text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        {{ t('auth.login') }}
                    </button>
                </div>
            </form>

            <!-- Register Link -->
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ t('auth.new_here') }}
                </p>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                <p>{{ t('auth.secure_auth') }}</p>
            </div>
        </div>
    </div>
</template>
