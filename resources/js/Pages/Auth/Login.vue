<script setup>
import Checkbox from '@/Components/Checkbox.vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <AuthLayout>
        <Head :title="t('auth.login')" />

        <!-- Header Section -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-md-on-surface">
                {{ t('auth.welcome_back') }}
            </h1>
            <p class="mt-2 text-sm text-md-on-surface-variant">
                {{ t('auth.secure_auth') }}
            </p>
        </div>

        <!-- Status Message -->
        <div
            v-if="status"
            class="mb-6 rounded-md bg-md-success-container p-4 text-center"
        >
            <p class="text-sm font-medium text-md-on-success-container">
                {{ status }}
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-md-surface-container rounded-md p-6 shadow-elevation-1">
            <form class="space-y-6" @submit.prevent="submit">
                <!-- Email Field -->
                <div>
                    <InputLabel for="email" :value="t('auth.email')" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div>
                    <InputLabel for="password" :value="t('auth.password')" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <label class="ms-2 text-sm text-md-on-surface-variant">
                        {{ t('auth.remember_me') }}
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-md-primary hover:text-md-primary-container transition-colors duration-medium2"
                    >
                        {{ t('auth.forgot_password') }}
                    </Link>

                    <PrimaryButton
                        class="ms-4"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ t('auth.login') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Register Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-md-on-surface-variant">
                {{ t('auth.new_here') }}
                <Link
                    :href="route('register')"
                    class="text-md-primary hover:text-md-primary-container transition-colors duration-medium2"
                >
                    {{ t('auth.register') }}
                </Link>
            </p>
        </div>
    </AuthLayout>
</template>
