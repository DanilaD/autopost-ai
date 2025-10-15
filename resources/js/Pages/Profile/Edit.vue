<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import Avatar from '@/Components/Avatar.vue'
import CompanyInfo from '@/Components/CompanyInfo.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    timezones: {
        type: Object,
        required: true,
    },
    commonTimezones: {
        type: Object,
        required: true,
    },
    company: {
        type: Object,
        default: null,
    },
})

const user = computed(() => page.props.auth.user)
</script>

<template>
    <Head :title="t('profile.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-900 dark:text-gray-100"
            >
                {{ t('profile.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Profile Header with Avatar -->
                <div class="rounded-md bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center space-x-6">
                        <!-- Avatar -->
                        <Avatar
                            :name="user.name"
                            size="xl"
                            :show-online="true"
                            :is-online="true"
                        />

                        <!-- User Info -->
                        <div class="flex-1">
                            <h1
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ user.name }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ user.email }}
                            </p>
                            <div
                                class="mt-2 flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400"
                            >
                                <span class="flex items-center">
                                    <svg
                                        class="w-4 h-4 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    {{ user.timezone || 'UTC' }}
                                </span>
                                <span class="flex items-center">
                                    <svg
                                        class="w-4 h-4 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    {{ t('profile.company.member_since') }}
                                    {{
                                        new Date(
                                            user.created_at
                                        ).toLocaleDateString()
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Information -->
                <div
                    v-if="company"
                    class="rounded-md bg-white p-6 shadow dark:bg-gray-800"
                >
                    <header class="mb-4">
                        <h2
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ t('profile.company.title') }}
                        </h2>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ t('profile.company.description') }}
                        </p>
                    </header>

                    <CompanyInfo :company="company" />
                </div>

                <!-- No Company Message -->
                <div
                    v-else
                    class="bg-md-warning-container border border-md-warning rounded-md p-6"
                >
                    <div class="flex items-center">
                        <svg
                            class="w-5 h-5 text-md-warning mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <p class="text-sm text-md-on-warning-container">
                            {{ t('profile.company.no_company') }}
                        </p>
                    </div>
                </div>

                <!-- Profile Information Form -->
                <div class="rounded-md bg-white p-6 shadow dark:bg-gray-800">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        :timezones="timezones"
                        :common-timezones="commonTimezones"
                        class="max-w-xl"
                    />
                </div>

                <!-- Update Password Form -->
                <div class="rounded-md bg-white p-6 shadow dark:bg-gray-800">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Delete Account Form -->
                <div class="rounded-md bg-white p-6 shadow dark:bg-gray-800">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
