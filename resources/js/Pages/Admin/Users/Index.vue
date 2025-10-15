<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Swal from 'sweetalert2'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SearchInput from '@/Components/SearchInput.vue'
import SortableHeader from '@/Components/SortableHeader.vue'
import Pagination from '@/Components/Pagination.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import Tooltip from '@/Components/Tooltip.vue'

const { t } = useI18n()

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
})

const search = ref(props.filters.search || '')
const processing = ref(false)

const sendPasswordReset = async (user) => {
    const result = await Swal.fire({
        title: t('admin.users.confirm_password_reset_title'),
        html: `${t('admin.users.confirm_password_reset_message')}<br><strong>${user.email}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('admin.users.confirm'),
        cancelButtonText: t('admin.users.cancel'),
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    processing.value = true

    router.post(
        route('admin.users.password-reset', user.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    title: t('admin.users.success'),
                    text: t('admin.users.password_reset_sent'),
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                })
            },
            onError: () => {
                Swal.fire({
                    title: t('admin.users.error'),
                    text: t('admin.users.action_failed'),
                    icon: 'error',
                })
            },
            onFinish: () => {
                processing.value = false
            },
        }
    )
}

const openSuspendModal = async (user) => {
    const result = await Swal.fire({
        title: t('admin.users.suspend_modal_title'),
        html: `<strong>${user.name}</strong><br>${t('admin.users.suspend_modal_message')}`,
        input: 'textarea',
        inputPlaceholder: t('admin.users.suspension_reason'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('admin.users.confirm_suspend'),
        cancelButtonText: t('admin.users.cancel'),
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return t('admin.users.suspension_reason') + ' is required'
            }
        },
    })

    if (!result.isConfirmed) {
        return
    }

    processing.value = true

    router.post(
        route('admin.users.suspend', user.id),
        {
            reason: result.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    title: t('admin.users.success'),
                    text: t('admin.users.user_suspended'),
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                })
            },
            onError: (errors) => {
                Swal.fire({
                    title: t('admin.users.error'),
                    text: errors.reason || t('admin.users.action_failed'),
                    icon: 'error',
                })
            },
            onFinish: () => {
                processing.value = false
            },
        }
    )
}

const unsuspendUser = async (user) => {
    const result = await Swal.fire({
        title: t('admin.users.confirm_unsuspend_title'),
        html: `<strong>${user.name}</strong><br>${t('admin.users.confirm_unsuspend_message')}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('admin.users.confirm'),
        cancelButtonText: t('admin.users.cancel'),
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    processing.value = true

    router.post(
        route('admin.users.unsuspend', user.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    title: t('admin.users.success'),
                    text: t('admin.users.user_unsuspended'),
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                })
            },
            onError: () => {
                Swal.fire({
                    title: t('admin.users.error'),
                    text: t('admin.users.action_failed'),
                    icon: 'error',
                })
            },
            onFinish: () => {
                processing.value = false
            },
        }
    )
}

const openImpersonateModal = async (user) => {
    const result = await Swal.fire({
        title: t('admin.users.confirm_impersonate_title'),
        html: `<strong>${user.name}</strong> (${user.email})<br><br>${t('admin.users.confirm_impersonate_message')}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#8b5cf6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('admin.users.confirm'),
        cancelButtonText: t('admin.users.cancel'),
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    processing.value = true

    router.post(
        route('admin.users.impersonate', user.id),
        {},
        {
            onSuccess: () => {
                Swal.fire({
                    title: t('admin.users.success'),
                    text: `${t('admin.users.impersonation_started')}: ${user.name}`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                })
            },
            onError: () => {
                Swal.fire({
                    title: t('admin.users.error'),
                    text: t('admin.users.action_failed'),
                    icon: 'error',
                })
            },
            onFinish: () => {
                processing.value = false
            },
        }
    )
}

const formatDate = (dateString) => {
    if (!dateString) return t('admin.users.never_logged_in')
    const parsed = new Date(dateString)
    if (isNaN(parsed.getTime())) return dateString

    const now = new Date()
    let diffDays = Math.floor((now.getTime() - parsed.getTime()) / 86400000)

    // Guard against negative offsets due to timezone parsing; treat as today
    if (diffDays <= 0) {
        return t('admin.inquiries.today')
    }
    if (diffDays === 1) {
        return '1 day ago'
    }
    if (diffDays < 7) {
        return `${diffDays} days ago`
    }
    if (diffDays < 30) {
        return `${Math.floor(diffDays / 7)} weeks ago`
    }
    return new Intl.DateTimeFormat('default', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(parsed)
}

const getRoleBadgeClass = (role) => {
    const classes = {
        admin: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        user: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        network:
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    }
    return (
        classes[role] ||
        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
    )
}

const getStatusBadgeClass = (isSuspended) => {
    return isSuspended
        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
        : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
}
</script>

<template>
    <Head :title="t('admin.users.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ t('admin.users.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="mb-6 flex gap-3 overflow-x-auto md:gap-4">
                    <Tooltip
                        :text="t('admin.users.tooltip_total_users')"
                        position="top"
                    >
                        <div
                            class="flex-1 cursor-help rounded-md bg-white p-3 shadow transition-all hover:shadow-elevation-2 dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-md-on-surface-variant md:text-sm"
                            >
                                {{ t('admin.users.total_users') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100 md:text-2xl"
                            >
                                {{ stats.total_users || 0 }}
                            </div>
                        </div>
                    </Tooltip>

                    <Tooltip
                        :text="t('admin.users.tooltip_active_users')"
                        position="top"
                    >
                        <div
                            class="flex-1 cursor-help rounded-md bg-white p-3 shadow transition-all hover:shadow-elevation-2 dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-md-on-surface-variant md:text-sm"
                            >
                                {{ t('admin.users.active_users') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-green-600 dark:text-green-400 md:text-2xl"
                            >
                                {{ stats.active_users || 0 }}
                            </div>
                        </div>
                    </Tooltip>

                    <Tooltip
                        :text="t('admin.users.tooltip_suspended_users')"
                        position="top"
                    >
                        <div
                            class="flex-1 cursor-help rounded-md bg-white p-3 shadow transition-all hover:shadow-elevation-2 dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-md-on-surface-variant md:text-sm"
                            >
                                {{ t('admin.users.suspended_users') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-red-600 dark:text-red-400 md:text-2xl"
                            >
                                {{ stats.suspended_users || 0 }}
                            </div>
                        </div>
                    </Tooltip>

                    <Tooltip
                        :text="t('admin.users.tooltip_new_this_month')"
                        position="top"
                    >
                        <div
                            class="flex-1 cursor-help rounded-md bg-white p-3 shadow transition-all hover:shadow-elevation-2 dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-md-on-surface-variant md:text-sm"
                            >
                                {{ t('admin.users.new_this_month') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-blue-600 dark:text-blue-400 md:text-2xl"
                            >
                                {{ stats.new_this_month || 0 }}
                            </div>
                        </div>
                    </Tooltip>
                </div>

                <!-- Search -->
                <div class="mb-6">
                    <div class="w-full sm:w-96">
                        <SearchInput
                            v-model="search"
                            :placeholder="t('admin.users.search_placeholder')"
                            route-name="admin.users.index"
                        />
                    </div>
                </div>

                <!-- Users Table -->
                <div
                    class="overflow-hidden rounded-md bg-white shadow dark:bg-gray-800"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead>
                                <tr>
                                    <SortableHeader
                                        field="name"
                                        :label="t('admin.users.name')"
                                        :current-sort="filters.sort"
                                        :current-direction="filters.direction"
                                    />
                                    <SortableHeader
                                        field="email"
                                        :label="t('admin.users.email')"
                                        :current-sort="filters.sort"
                                        :current-direction="filters.direction"
                                    />
                                    <th
                                        class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                    >
                                        {{ t('admin.users.role') }}
                                    </th>
                                    <th
                                        class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                    >
                                        {{ t('admin.users.status') }}
                                    </th>
                                    <SortableHeader
                                        field="last_login_at"
                                        :label="t('admin.users.last_login')"
                                        :current-sort="filters.sort"
                                        :current-direction="filters.direction"
                                    />
                                    <th
                                        class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                    >
                                        {{ t('admin.users.stats') }}
                                    </th>
                                    <th
                                        class="bg-gray-50 px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                    >
                                        {{ t('admin.users.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"
                            >
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ user.name }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ user.email }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <span
                                            v-if="user.role_in_current_company"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            :class="
                                                getRoleBadgeClass(
                                                    user.role_in_current_company
                                                )
                                            "
                                        >
                                            {{ user.role_in_current_company }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-gray-400 dark:text-gray-600"
                                        >
                                            N/A
                                        </span>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            :class="
                                                getStatusBadgeClass(
                                                    user.is_suspended
                                                )
                                            "
                                        >
                                            {{
                                                user.is_suspended
                                                    ? t('admin.users.suspended')
                                                    : t('admin.users.active')
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ formatDate(user.last_login_at) }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        <div class="flex gap-3">
                                            <div
                                                :title="
                                                    t(
                                                        'admin.users.companies_count'
                                                    )
                                                "
                                            >
                                                🏢
                                                {{ user.stats.companies_count }}
                                            </div>
                                            <div
                                                :title="
                                                    t(
                                                        'admin.users.instagram_accounts'
                                                    )
                                                "
                                            >
                                                📸
                                                {{
                                                    user.stats
                                                        .instagram_accounts_count
                                                }}
                                            </div>
                                            <div
                                                :title="
                                                    t('admin.users.posts_count')
                                                "
                                            >
                                                📝 {{ user.stats.posts_count }}
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-sm"
                                    >
                                        <div class="flex justify-end gap-2">
                                            <button
                                                :disabled="processing"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 disabled:opacity-50"
                                                :title="
                                                    t(
                                                        'admin.users.send_password_reset'
                                                    )
                                                "
                                                @click="sendPasswordReset(user)"
                                            >
                                                🔑
                                            </button>
                                            <button
                                                v-if="!user.is_suspended"
                                                :disabled="processing"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-50"
                                                :title="
                                                    t('admin.users.suspend')
                                                "
                                                @click="openSuspendModal(user)"
                                            >
                                                🚫
                                            </button>
                                            <button
                                                v-else
                                                :disabled="processing"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 disabled:opacity-50"
                                                :title="
                                                    t('admin.users.unsuspend')
                                                "
                                                @click="unsuspendUser(user)"
                                            >
                                                ✅
                                            </button>
                                            <button
                                                :disabled="
                                                    processing ||
                                                    user.is_suspended
                                                "
                                                class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 disabled:opacity-50"
                                                :title="
                                                    t('admin.users.impersonate')
                                                "
                                                @click="
                                                    openImpersonateModal(user)
                                                "
                                            >
                                                🎭
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-if="users.data.length === 0"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        colspan="7"
                                        class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ t('admin.users.no_results') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
