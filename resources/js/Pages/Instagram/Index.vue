<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'
import { onMounted } from 'vue'
import Swal from 'sweetalert2'

const { t } = useI18n()

const props = defineProps({
    accounts: {
        type: Array,
        required: true,
    },
    hasCompany: {
        type: Boolean,
        required: true,
    },
    configError: {
        type: String,
        default: null,
    },
})

const toast = useToast()

// Show configuration error if Instagram is not set up
onMounted(() => {
    if (props.configError) {
        toast.addToast(props.configError, 'error', 10000) // Show for 10 seconds
    }
})

const disconnectAccount = async (account) => {
    const result = await Swal.fire({
        title: t('instagram.disconnect_confirm_title'),
        html: t('instagram.disconnect_confirm_message', {
            username: `<strong>@${account.username}</strong>`,
        }),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('instagram.disconnect_button'),
        cancelButtonText: t('instagram.disconnect_cancel'),
        reverseButtons: true,
        customClass: {
            popup: 'dark:bg-gray-800',
            title: 'dark:text-gray-100',
            htmlContainer: 'dark:text-gray-300',
            confirmButton: 'px-4 py-2 rounded-md',
            cancelButton: 'px-4 py-2 rounded-md',
        },
    })

    if (result.isConfirmed) {
        router.post(
            route('instagram.disconnect', account.id),
            {},
            {
                preserveScroll: true,
            }
        )
    }
}

const syncAccount = (account) => {
    router.post(
        route('instagram.sync', account.id),
        {},
        {
            preserveScroll: true,
        }
    )
}

const getStatusBadgeClass = (account) => {
    if (account.is_token_expired)
        return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
    if (account.is_token_expiring_soon)
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
    if (account.is_active)
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
    return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getStatusText = (account) => {
    if (account.is_token_expired) return t('instagram.status_expired')
    if (account.is_token_expiring_soon)
        return t('instagram.status_expiring_soon')
    if (account.is_active) return t('instagram.status_active')
    return account.status
}
</script>

<template>
    <Head :title="t('instagram.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                >
                    {{ t('instagram.title') }}
                </h2>
                <a
                    v-if="hasCompany"
                    :href="route('instagram.connect')"
                    class="inline-flex items-center px-4 py-2 bg-pink-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-pink-700 active:bg-pink-900 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg
                        class="w-4 h-4 mr-2"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                        />
                    </svg>
                    {{ t('instagram.connect_button') }}
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- No Company Warning -->
                <div
                    v-if="!hasCompany"
                    class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-500 p-4"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-yellow-400 dark:text-yellow-500"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p
                                class="text-sm text-yellow-700 dark:text-yellow-400"
                            >
                                {{ t('instagram.no_active_company') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Connected Accounts -->
                <div v-if="accounts.length > 0" class="space-y-4">
                    <div
                        v-for="account in accounts"
                        :key="account.id"
                        class="bg-md-surface-container overflow-hidden shadow-elevation-1 rounded-md"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <!-- Account Info -->
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="flex-shrink-0 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full p-3"
                                    >
                                        <svg
                                            class="h-8 w-8 text-white"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            @{{ account.username }}
                                        </h3>
                                        <div
                                            class="flex items-center space-x-3 mt-1"
                                        >
                                            <span
                                                :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                    getStatusBadgeClass(
                                                        account
                                                    ),
                                                ]"
                                            >
                                                {{ getStatusText(account) }}
                                            </span>
                                            <span
                                                class="text-sm text-gray-500 dark:text-gray-400 capitalize"
                                            >
                                                {{ account.account_type }}
                                            </span>
                                        </div>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                                        >
                                            {{ t('instagram.connected') }}
                                            {{ account.created_at }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        @click="syncAccount(account)"
                                    >
                                        <svg
                                            class="h-4 w-4 mr-1"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                            />
                                        </svg>
                                        {{ t('instagram.sync') }}
                                    </button>
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-700 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 dark:text-red-400 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        @click="disconnectAccount(account)"
                                    >
                                        {{ t('instagram.disconnect') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Warning for expiring tokens -->
                            <div
                                v-if="account.is_token_expiring_soon"
                                class="mt-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-500 p-4"
                            >
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-5 w-5 text-yellow-400 dark:text-yellow-500"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p
                                            class="text-sm text-yellow-700 dark:text-yellow-400"
                                        >
                                            {{ t('instagram.token_warning') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="accounts.length === 0 && hasCompany"
                    class="bg-md-surface-container overflow-hidden shadow-elevation-1 rounded-md"
                >
                    <div class="p-12 text-center">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                            />
                        </svg>
                        <h3
                            class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ t('instagram.no_accounts') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ t('instagram.connect_description') }}
                        </p>
                        <div class="mt-6">
                            <a
                                :href="route('instagram.connect')"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                                    />
                                </svg>
                                {{ t('instagram.connect_button') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
