<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, useForm } from '@inertiajs/vue3'
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
    <AuthLayout>
        <Head :title="t('auth.forgot_password_title')" />

        <!-- Header Section -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-md-on-surface">
                {{ t('auth.forgot_password_title') }}
            </h1>
            <p class="mt-2 text-sm text-md-on-surface-variant">
                {{ t('auth.forgot_password_description') }}
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

                <div class="flex items-center justify-end">
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ t('auth.email_password_reset_link') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Back to Login Link -->
        <div class="mt-6 text-center">
            <a
                :href="route('login')"
                class="text-sm text-md-primary hover:text-md-primary-container transition-colors duration-medium2"
            >
                ← {{ t('auth.back_to_login') }}
            </a>
        </div>
    </AuthLayout>
</template>
