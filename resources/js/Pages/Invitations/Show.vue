<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import LanguageSelector from '@/Components/LanguageSelector.vue'

const props = defineProps({
    invitation: Object,
    company: Object,
    inviter: Object,
    userExists: Boolean,
    registerUrl: String,
    loginUrl: String,
    message: String,
})

const { t } = useI18n()

const roleLabels = {
    admin: t('emails.company_invitation.role_admin'),
    user: t('emails.company_invitation.role_user'),
    network: t('emails.company_invitation.role_network'),
}

const roleLabel = computed(
    () => roleLabels[props.invitation.role] || props.invitation.role
)

const formatExpiryDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <GuestLayout>
        <Head :title="$t('company.invitation.title')" />

        <div
            class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50"
        >
            <!-- Language Selector -->
            <div class="absolute top-4 right-4">
                <LanguageSelector />
            </div>

            <div
                class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
            >
                <!-- Header -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        {{ $t('company.invitation.welcome_title') }}
                    </h1>
                    <p class="text-gray-600">
                        {{ $t('company.invitation.welcome_subtitle') }}
                    </p>
                </div>

                <!-- Message -->
                <div
                    v-if="message"
                    class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md"
                >
                    <p class="text-sm text-blue-800">
                        {{ message }}
                    </p>
                </div>

                <!-- Company Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">
                        {{ $t('company.invitation.company_info') }}
                    </h2>

                    <div class="space-y-2">
                        <div>
                            <span class="text-sm font-medium text-gray-600">
                                {{ $t('company.invitation.company_name') }}:
                            </span>
                            <span class="text-sm text-gray-900 ml-2">{{
                                company.name
                            }}</span>
                        </div>

                        <div>
                            <span class="text-sm font-medium text-gray-600">
                                {{ $t('company.invitation.invited_by') }}:
                            </span>
                            <span class="text-sm text-gray-900 ml-2">{{
                                inviter.name
                            }}</span>
                        </div>

                        <div>
                            <span class="text-sm font-medium text-gray-600">
                                {{ $t('company.invitation.your_role') }}:
                            </span>
                            <span
                                class="inline-block ml-2 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                            >
                                {{ roleLabel }}
                            </span>
                        </div>

                        <div>
                            <span class="text-sm font-medium text-gray-600">
                                {{ $t('company.invitation.email') }}:
                            </span>
                            <span class="text-sm text-gray-900 ml-2">{{
                                invitation.email
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Expiry Notice -->
                <div
                    class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-6"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-yellow-400"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                {{ $t('company.invitation.expiry_title') }}
                            </h3>
                            <div class="mt-1 text-sm text-yellow-700">
                                {{
                                    $t('company.invitation.expiry_message', {
                                        expires_at: formatExpiryDate(
                                            invitation.expires_at
                                        ),
                                    })
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <!-- If user exists, show login button -->
                    <div v-if="userExists">
                        <p class="text-sm text-gray-600 mb-3">
                            {{ $t('company.invitation.user_exists_message') }}
                        </p>
                        <Link :href="loginUrl" class="w-full">
                            <PrimaryButton class="w-full">
                                {{ $t('company.invitation.login_button') }}
                            </PrimaryButton>
                        </Link>
                    </div>

                    <!-- If user doesn't exist, show register button -->
                    <div v-else>
                        <p class="text-sm text-gray-600 mb-3">
                            {{
                                $t('company.invitation.user_not_exists_message')
                            }}
                        </p>
                        <Link :href="registerUrl" class="w-full">
                            <PrimaryButton class="w-full">
                                {{ $t('company.invitation.register_button') }}
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500">
                        {{ $t('company.invitation.footer_message') }}
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
