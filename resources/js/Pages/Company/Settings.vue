<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useI18n } from 'vue-i18n'
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const toast = useToast()

const props = defineProps({
    company: {
        type: Object,
        default: () => ({}),
    },
    teamMembers: {
        type: Array,
        default: () => [],
    },
    invitations: {
        type: Object,
        default: () => ({}),
    },
    canManageUsers: {
        type: Boolean,
        default: false,
    },
})

const showInviteForm = ref(false)
const showRoleModal = ref(false)
const selectedUser = ref(null)

const inviteForm = useForm({
    email: '',
    role: 'user',
})

const roleForm = useForm({
    role: '',
})

const submitInvitation = () => {
    inviteForm.post(route('companies.invite'), {
        onSuccess: () => {
            inviteForm.reset()
            showInviteForm.value = false
            // Toast message will be handled by backend
        },
        onError: () => {
            // Error toast will be handled by backend
        },
    })
}

const updateUserRole = (user) => {
    selectedUser.value = user
    roleForm.role = user.role
    showRoleModal.value = true
}

const submitRoleUpdate = () => {
    roleForm.put(route('companies.users.update-role', selectedUser.value.id), {
        onSuccess: () => {
            showRoleModal.value = false
            selectedUser.value = null
            // Toast message will be handled by backend
        },
        onError: () => {
            // Error toast will be handled by backend
        },
    })
}

const removeUser = (user) => {
    Swal.fire({
        title: t('common.confirm'),
        text: t('company.settings.confirm_remove_user', { name: user.name }),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('common.yes'),
        cancelButtonText: t('common.no'),
        allowOutsideClick: true,
        allowEscapeKey: true,
        allowEnterKey: true,
        reverseButtons: false,
        focusCancel: false,
        focusConfirm: true,
        returnFocus: true,
        heightAuto: true,
        width: '320px',
        padding: '1rem',
        customClass: {
            popup: 'swal2-popup !w-[320px] !p-4',
            container: 'swal2-container',
            title: 'swal2-title !text-base !mb-2',
            htmlContainer: 'swal2-html-container !text-sm !mb-3',
            actions: 'swal2-actions !gap-2 !mt-3',
            confirmButton: 'swal2-confirm !px-3 !py-1.5 !text-sm',
            cancelButton: 'swal2-cancel !px-3 !py-1.5 !text-sm',
            loader: 'swal2-loader',
            footer: 'swal2-footer',
            timerProgressBar: 'swal2-timer-progress-bar',
            closeButton: 'swal2-close',
        },
        buttonsStyling: true,
        showClass: {
            popup: 'swal2-show',
            backdrop: 'swal2-backdrop-show',
            icon: 'swal2-icon-show',
        },
        hideClass: {
            popup: 'swal2-hide',
            backdrop: 'swal2-backdrop-hide',
            icon: 'swal2-icon-hide',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('companies.users.remove', user.id), {
                onSuccess: () => {
                    toast.success('User removed successfully!')
                },
                onError: (errors) => {
                    toast.error('Failed to remove user')
                },
            })
        }
    })
}

const cancelInvitation = (invitation) => {
    Swal.fire({
        title: t('common.confirm'),
        text: t('company.settings.confirm_cancel_invitation', {
            email: invitation.email,
        }),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('common.yes'),
        cancelButtonText: t('common.no'),
        allowOutsideClick: true,
        allowEscapeKey: true,
        allowEnterKey: true,
        reverseButtons: false,
        focusCancel: false,
        focusConfirm: true,
        returnFocus: true,
        heightAuto: true,
        width: '320px',
        padding: '1rem',
        customClass: {
            popup: 'swal2-popup !w-[320px] !p-4',
            container: 'swal2-container',
            title: 'swal2-title !text-base !mb-2',
            htmlContainer: 'swal2-html-container !text-sm !mb-3',
            actions: 'swal2-actions !gap-2 !mt-3',
            confirmButton: 'swal2-confirm !px-3 !py-1.5 !text-sm',
            cancelButton: 'swal2-cancel !px-3 !py-1.5 !text-sm',
            loader: 'swal2-loader',
            footer: 'swal2-footer',
            timerProgressBar: 'swal2-timer-progress-bar',
            closeButton: 'swal2-close',
        },
        buttonsStyling: true,
        showClass: {
            popup: 'swal2-show',
            backdrop: 'swal2-backdrop-show',
            icon: 'swal2-icon-show',
        },
        hideClass: {
            popup: 'swal2-hide',
            backdrop: 'swal2-backdrop-hide',
            icon: 'swal2-icon-hide',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(
                route('companies.invitations.cancel', invitation.id),
                {
                    onSuccess: () => {
                        // Toast message will be handled by backend
                    },
                    onError: () => {
                        // Error toast will be handled by backend
                    },
                }
            )
        }
    })
}

const resendInvitation = (invitation) => {
    Swal.fire({
        title: t('company.settings.resend_invitation'),
        text: invitation.email,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4b5563',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('common.yes'),
        cancelButtonText: t('common.no'),
        allowOutsideClick: true,
        allowEscapeKey: true,
        allowEnterKey: true,
        reverseButtons: false,
        focusCancel: false,
        focusConfirm: true,
        returnFocus: true,
        heightAuto: true,
        width: '320px',
        padding: '1rem',
        customClass: {
            popup: 'swal2-popup !w-[320px] !p-4',
            container: 'swal2-container',
            title: 'swal2-title !text-base !mb-2',
            htmlContainer: 'swal2-html-container !text-sm !mb-3',
            actions: 'swal2-actions !gap-2 !mt-3',
            confirmButton: 'swal2-confirm !px-3 !py-1.5 !text-sm',
            cancelButton: 'swal2-cancel !px-3 !py-1.5 !text-sm',
            loader: 'swal2-loader',
            footer: 'swal2-footer',
            timerProgressBar: 'swal2-timer-progress-bar',
            closeButton: 'swal2-close',
        },
        buttonsStyling: true,
        showClass: {
            popup: 'swal2-show',
            backdrop: 'swal2-backdrop-show',
            icon: 'swal2-icon-show',
        },
        hideClass: {
            popup: 'swal2-hide',
            backdrop: 'swal2-backdrop-hide',
            icon: 'swal2-icon-hide',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('companies.invitations.resend', invitation.id), {
                onSuccess: () => {
                    // Toast handled by backend
                },
                onError: () => {
                    // Error toast handled by backend
                },
                preserveScroll: true,
            })
        }
    })
}
</script>

<template>
    <Head :title="t('company.settings.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ t('company.settings.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <!-- Company Information -->
                    <div
                        class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg"
                    >
                        <div class="p-6">
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-white mb-4"
                            >
                                {{ t('company.settings.company_info') }}
                            </h3>
                            <dl
                                class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                            >
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        {{ t('company.settings.company_name') }}
                                    </dt>
                                    <dd
                                        class="mt-1 text-sm text-gray-900 dark:text-white"
                                    >
                                        {{ company.name }}
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        {{ t('company.settings.created_at') }}
                                    </dt>
                                    <dd
                                        class="mt-1 text-sm text-gray-900 dark:text-white"
                                    >
                                        {{
                                            new Date(
                                                company.created_at
                                            ).toLocaleDateString()
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Team Members -->
                    <div
                        class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-white"
                                >
                                    {{ t('company.settings.team_members') }}
                                </h3>
                                <button
                                    v-if="canManageUsers"
                                    class="btn-glass-neutral inline-flex items-center px-4 py-2 text-sm font-medium transition-all duration-200 hover:transform hover:-translate-y-0.5"
                                    @click="showInviteForm = !showInviteForm"
                                >
                                    <svg
                                        class="w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                        />
                                    </svg>
                                    {{ t('company.settings.invite_member') }}
                                </button>
                            </div>

                            <!-- Team Members List -->
                            <div
                                class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
                            >
                                <table
                                    class="min-w-full divide-y divide-gray-300 dark:divide-gray-600"
                                >
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{
                                                    t('company.settings.member')
                                                }}
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{ t('company.settings.role') }}
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{
                                                    t('company.settings.joined')
                                                }}
                                            </th>
                                            <th
                                                v-if="canManageUsers"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{
                                                    t(
                                                        'company.settings.actions'
                                                    )
                                                }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600"
                                    >
                                        <tr
                                            v-for="member in teamMembers"
                                            :key="member.id"
                                        >
                                            <td
                                                class="px-6 py-4 whitespace-nowrap"
                                            >
                                                <div class="flex items-center">
                                                    <div
                                                        class="flex-shrink-0 h-10 w-10"
                                                    >
                                                        <div
                                                            class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center"
                                                        >
                                                            <span
                                                                class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                                            >
                                                                {{
                                                                    member.name
                                                                        .charAt(
                                                                            0
                                                                        )
                                                                        .toUpperCase()
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div
                                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                                        >
                                                            {{ member.name }}
                                                            <span
                                                                v-if="
                                                                    member.is_owner
                                                                "
                                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                                                            >
                                                                {{
                                                                    t(
                                                                        'company.settings.owner'
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="text-sm text-gray-500 dark:text-gray-400"
                                                        >
                                                            {{ member.email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap"
                                            >
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                            member.role ===
                                                            'admin',
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
                                                            member.role ===
                                                            'user',
                                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200':
                                                            member.role ===
                                                            'network',
                                                    }"
                                                >
                                                    {{
                                                        t(
                                                            `company.settings.role_${member.role}`
                                                        )
                                                    }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    new Date(
                                                        member.joined_at
                                                    ).toLocaleDateString()
                                                }}
                                            </td>
                                            <td
                                                v-if="canManageUsers"
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                            >
                                                <div
                                                    v-if="!member.is_owner"
                                                    class="flex items-center space-x-3"
                                                >
                                                    <button
                                                        class="text-pattern-primary hover:text-pattern-primary-dark dark:text-pattern-primary-light dark:hover:text-pattern-primary transition-colors duration-200 inline-flex items-center"
                                                        :title="
                                                            t(
                                                                'company.settings.edit_role'
                                                            )
                                                        "
                                                        :aria-label="
                                                            t(
                                                                'company.settings.edit_role'
                                                            )
                                                        "
                                                        @click="
                                                            updateUserRole(
                                                                member
                                                            )
                                                        "
                                                    >
                                                        <svg
                                                            class="w-5 h-5"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                            aria-hidden="true"
                                                        >
                                                            <!-- Edit/Pencil icon -->
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                            />
                                                        </svg>
                                                        <span class="sr-only">{{
                                                            t(
                                                                'company.settings.edit_role'
                                                            )
                                                        }}</span>
                                                    </button>
                                                    <button
                                                        class="text-pattern-error hover:text-pattern-error-dark dark:text-pattern-error-light dark:hover:text-pattern-error transition-colors duration-200 inline-flex items-center"
                                                        :title="
                                                            t(
                                                                'company.settings.remove'
                                                            )
                                                        "
                                                        :aria-label="
                                                            t(
                                                                'company.settings.remove'
                                                            )
                                                        "
                                                        @click="
                                                            removeUser(member)
                                                        "
                                                    >
                                                        <svg
                                                            class="w-5 h-5"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                            aria-hidden="true"
                                                        >
                                                            <!-- Trash/Delete icon -->
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                            />
                                                        </svg>
                                                        <span class="sr-only">{{
                                                            t(
                                                                'company.settings.remove'
                                                            )
                                                        }}</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Invitations -->
                    <div
                        v-if="invitations.data.length > 0"
                        class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg"
                    >
                        <div class="p-6">
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-white mb-4"
                            >
                                {{ t('company.settings.pending_invitations') }}
                            </h3>
                            <div
                                class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
                            >
                                <table
                                    class="min-w-full divide-y divide-gray-300 dark:divide-gray-600"
                                >
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{
                                                    t('company.settings.email')
                                                }}
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{ t('company.settings.role') }}
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{
                                                    t(
                                                        'company.settings.invited_by'
                                                    )
                                                }}
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{
                                                    t(
                                                        'company.settings.expires'
                                                    )
                                                }}
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                            >
                                                {{
                                                    t(
                                                        'company.settings.actions'
                                                    )
                                                }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600"
                                    >
                                        <tr
                                            v-for="invitation in invitations.data"
                                            :key="invitation.id"
                                        >
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                                            >
                                                {{ invitation.email }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap"
                                            >
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                            invitation.role ===
                                                            'admin',
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
                                                            invitation.role ===
                                                            'user',
                                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200':
                                                            invitation.role ===
                                                            'network',
                                                    }"
                                                >
                                                    {{
                                                        t(
                                                            `company.settings.role_${invitation.role}`
                                                        )
                                                    }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{ invitation.inviter.name }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    new Date(
                                                        invitation.expires_at
                                                    ).toLocaleDateString()
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                            >
                                                <div
                                                    class="flex items-center space-x-3"
                                                >
                                                    <button
                                                        class="text-pattern-primary hover:text-pattern-primary-dark dark:text-pattern-primary-light dark:hover:text-pattern-primary inline-flex items-center transition-colors duration-200"
                                                        :title="
                                                            t(
                                                                'company.settings.resend_invitation'
                                                            )
                                                        "
                                                        :aria-label="
                                                            t(
                                                                'company.settings.resend_invitation'
                                                            )
                                                        "
                                                        @click="
                                                            resendInvitation(
                                                                invitation
                                                            )
                                                        "
                                                    >
                                                        <svg
                                                            class="w-5 h-5"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                            aria-hidden="true"
                                                        >
                                                            <!-- Paper Airplane (send) icon -->
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 19l9-7-9-7-9 7 9 7z"
                                                            />
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 12l9-7"
                                                            />
                                                        </svg>
                                                        <span class="sr-only">{{
                                                            t(
                                                                'company.settings.resend_invitation'
                                                            )
                                                        }}</span>
                                                    </button>
                                                    <button
                                                        class="text-pattern-error hover:text-pattern-error-dark dark:text-pattern-error-light dark:hover:text-pattern-error transition-colors duration-200 inline-flex items-center"
                                                        :title="
                                                            t(
                                                                'company.settings.cancel'
                                                            )
                                                        "
                                                        :aria-label="
                                                            t(
                                                                'company.settings.cancel'
                                                            )
                                                        "
                                                        @click="
                                                            cancelInvitation(
                                                                invitation
                                                            )
                                                        "
                                                    >
                                                        <svg
                                                            class="w-5 h-5"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                            aria-hidden="true"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M6 18L18 6M6 6l12 12"
                                                            />
                                                        </svg>
                                                        <span class="sr-only">{{
                                                            t(
                                                                'company.settings.cancel'
                                                            )
                                                        }}</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Update Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 transition-opacity"
                    aria-hidden="true"
                >
                    <div class="absolute inset-0 bg-gray-500 opacity-75" />
                </div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700"
                    style="border-radius: 0"
                >
                    <form @submit.prevent="submitRoleUpdate">
                        <div
                            class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4"
                        >
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4"
                            >
                                {{ t('company.settings.update_role') }}
                            </h3>
                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mb-4"
                            >
                                {{
                                    t(
                                        'company.settings.update_role_description',
                                        { name: selectedUser?.name }
                                    )
                                }}
                            </p>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('company.settings.role') }}
                                </label>
                                <select
                                    v-model="roleForm.role"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="user">
                                        {{ t('company.settings.role_user') }}
                                    </option>
                                    <option value="admin">
                                        {{ t('company.settings.role_admin') }}
                                    </option>
                                    <option value="network">
                                        {{ t('company.settings.role_network') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700"
                        >
                            <button
                                type="submit"
                                :disabled="roleForm.processing"
                                class="btn-glass-primary w-full inline-flex justify-center px-4 py-2 text-base font-medium transition-all duration-200 hover:transform hover:-translate-y-0.5 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:transform-none"
                            >
                                {{ t('company.settings.update') }}
                            </button>
                            <button
                                type="button"
                                class="btn-glass-neutral mt-3 w-full inline-flex justify-center px-4 py-2 text-base font-medium transition-all duration-200 hover:transform hover:-translate-y-0.5 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                :title="t('common.cancel')"
                                @click="showRoleModal = false"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Invite Member Modal -->
        <div v-if="showInviteForm" class="fixed inset-0 z-50 overflow-y-auto">
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 transition-opacity"
                    aria-hidden="true"
                >
                    <div class="absolute inset-0 bg-gray-500 opacity-75" />
                </div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700"
                    style="border-radius: 0"
                >
                    <form @submit.prevent="submitInvitation">
                        <div
                            class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4"
                        >
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4"
                            >
                                {{ t('company.settings.invite_new_member') }}
                            </h3>
                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mb-4"
                            >
                                {{ t('company.settings.invite_description') }}
                            </p>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('company.settings.email') }}
                                </label>
                                <input
                                    v-model="inviteForm.email"
                                    type="email"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    :placeholder="
                                        t('company.settings.email_placeholder')
                                    "
                                />
                            </div>
                            <div class="mt-4">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('company.settings.role') }}
                                </label>
                                <select
                                    v-model="inviteForm.role"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="user">
                                        {{ t('company.settings.role_user') }}
                                    </option>
                                    <option value="admin">
                                        {{ t('company.settings.role_admin') }}
                                    </option>
                                    <option value="network">
                                        {{ t('company.settings.role_network') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700"
                        >
                            <button
                                type="submit"
                                :disabled="inviteForm.processing"
                                class="btn-glass-primary w-full inline-flex justify-center px-4 py-2 text-base font-medium transition-all duration-200 hover:transform hover:-translate-y-0.5 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:transform-none"
                            >
                                {{ t('company.settings.send_invitation') }}
                            </button>
                            <button
                                type="button"
                                class="btn-glass-neutral mt-3 w-full inline-flex justify-center px-4 py-2 text-base font-medium transition-all duration-200 hover:transform hover:-translate-y-0.5 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                @click="showInviteForm = false"
                            >
                                {{ t('common.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
