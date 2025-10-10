<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref, computed, watch } from 'vue'

const props = defineProps({
    email: String,
    mode: String, // 'register' or 'login'
    message: String,
})

const { t } = useI18n()

const currentStep = ref(props.mode || 'email')

const emailForm = useForm({
    email: props.email || '',
})

const registerForm = useForm({
    name: '',
    email: props.email || '',
    password: '',
    password_confirmation: '',
})

const loginForm = useForm({
    email: props.email || '',
    password: '',
    remember: false,
})

const isEmailStep = computed(() => currentStep.value === 'email')
const isRegisterStep = computed(() => currentStep.value === 'register')
const isLoginStep = computed(() => currentStep.value === 'login')

const submitEmail = () => {
    emailForm.post(route('auth.email.check'), {
        onSuccess: () => {
            // Will be redirected back with mode
        },
    })
}

const submitRegister = () => {
    registerForm.post(route('register'), {
        onFinish: () => registerForm.reset('password', 'password_confirmation'),
    })
}

const submitLogin = () => {
    loginForm.post(route('login'), {
        onFinish: () => loginForm.reset('password'),
    })
}

const goBack = () => {
    currentStep.value = 'email'
    emailForm.reset()
    registerForm.reset()
    loginForm.reset()
}

// Watch for prop changes and update form data
watch(
    () => props.mode,
    (newMode) => {
        if (newMode) {
            currentStep.value = newMode
        }
    },
    { immediate: true }
)

watch(
    () => props.email,
    (newEmail) => {
        if (newEmail) {
            emailForm.email = newEmail
            registerForm.email = newEmail
            loginForm.email = newEmail
        }
    },
    { immediate: true }
)
</script>

<template>
    <Head :title="t('auth.welcome_back')" />

    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100"
    >
        <div
            class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg"
        >
            <!-- Logo/Brand -->
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900">Autopost AI</h1>
                <p v-if="isEmailStep" class="mt-3 text-gray-600">
                    {{ t('auth.enter_email') }}
                </p>
                <p v-else-if="isRegisterStep" class="mt-3 text-gray-600">
                    {{ message || t('auth.new_here') }}
                </p>
                <p v-else-if="isLoginStep" class="mt-3 text-gray-600">
                    {{ message || t('auth.welcome_back') }}
                </p>
            </div>

            <!-- Email Form (Step 1) -->
            <form
                v-if="isEmailStep"
                class="mt-8 space-y-6"
                @submit.prevent="submitEmail"
            >
                <div>
                    <label for="email" class="sr-only">
                        {{ t('auth.email') }}
                    </label>
                    <input
                        id="email"
                        v-model="emailForm.email"
                        type="email"
                        required
                        autofocus
                        class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                        :placeholder="t('auth.email')"
                    />
                    <div
                        v-if="emailForm.errors.email"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ emailForm.errors.email }}
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="emailForm.processing"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ t('auth.continue') }}
                    </button>
                </div>
            </form>

            <!-- Registration Form (Step 2a) -->
            <form
                v-if="isRegisterStep"
                class="mt-8 space-y-6"
                @submit.prevent="submitRegister"
            >
                <div class="space-y-4">
                    <div>
                        <label for="name" class="sr-only">Name</label>
                        <input
                            id="name"
                            v-model="registerForm.name"
                            type="text"
                            required
                            autofocus
                            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Full Name"
                        />
                        <div
                            v-if="registerForm.errors.name"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ registerForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label for="register-email" class="sr-only">
                            {{ t('auth.email') }}
                        </label>
                        <input
                            id="register-email"
                            v-model="registerForm.email"
                            type="email"
                            required
                            readonly
                            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg bg-gray-50"
                            :placeholder="t('auth.email')"
                        />
                    </div>

                    <div>
                        <label for="password" class="sr-only">
                            {{ t('auth.password') }}
                        </label>
                        <input
                            id="password"
                            v-model="registerForm.password"
                            type="password"
                            required
                            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            :placeholder="t('auth.password')"
                        />
                        <div
                            v-if="registerForm.errors.password"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ registerForm.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="sr-only">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="registerForm.password_confirmation"
                            type="password"
                            required
                            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Confirm Password"
                        />
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button
                        type="button"
                        class="flex-1 py-3 px-4 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        :disabled="registerForm.processing"
                        class="flex-1 py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        Register
                    </button>
                </div>
            </form>

            <!-- Login Form (Step 2b) -->
            <form
                v-if="isLoginStep"
                class="mt-8 space-y-6"
                @submit.prevent="submitLogin"
            >
                <div class="space-y-4">
                    <div>
                        <label for="login-email" class="sr-only">
                            {{ t('auth.email') }}
                        </label>
                        <input
                            id="login-email"
                            v-model="loginForm.email"
                            type="email"
                            required
                            readonly
                            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg bg-gray-50"
                            :placeholder="t('auth.email')"
                        />
                    </div>

                    <div>
                        <label for="login-password" class="sr-only">
                            {{ t('auth.password') }}
                        </label>
                        <input
                            id="login-password"
                            v-model="loginForm.password"
                            type="password"
                            required
                            autofocus
                            class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            :placeholder="t('auth.password')"
                        />
                        <div
                            v-if="loginForm.errors.password"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ loginForm.errors.password }}
                        </div>
                        <div
                            v-if="loginForm.errors.email"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ loginForm.errors.email }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input
                                v-model="loginForm.remember"
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <span class="ml-2 text-sm text-gray-600">
                                {{ t('auth.remember_me') }}
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button
                        type="button"
                        class="flex-1 py-3 px-4 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        :disabled="loginForm.processing"
                        class="flex-1 py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        {{ t('auth.login') }}
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500">
                <p>Secure authentication powered by Autopost AI</p>
            </div>
        </div>
    </div>
</template>
