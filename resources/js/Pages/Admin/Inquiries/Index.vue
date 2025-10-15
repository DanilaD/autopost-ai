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
    inquiries: {
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
const deleting = ref(null)

const deleteInquiry = async (id) => {
    const result = await Swal.fire({
        title: t('admin.inquiries.delete_confirm_title'),
        text: t('admin.inquiries.delete_confirm_message'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: t('admin.inquiries.confirm_delete'),
        cancelButtonText: t('admin.inquiries.cancel'),
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    deleting.value = id

    router.delete(route('admin.inquiries.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: t('admin.inquiries.deleted_success'),
                text: t('admin.inquiries.deleted_message'),
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
            })
        },
        onError: () => {
            Swal.fire({
                title: t('admin.inquiries.delete_error'),
                text: t('admin.inquiries.delete_error_message'),
                icon: 'error',
            })
        },
        onFinish: () => {
            deleting.value = null
        },
    })
}

const exportInquiries = () => {
    window.location.href = route('admin.inquiries.export', {
        search: props.filters.search,
        sort: props.filters.sort,
        direction: props.filters.direction,
    })
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('default', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

const truncate = (str, length = 50) => {
    if (!str) return 'N/A'
    return str.length > length ? str.substring(0, length) + '...' : str
}
</script>

<template>
    <Head :title="t('admin.inquiries.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ t('admin.inquiries.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="mb-6 flex gap-3 overflow-x-auto md:gap-4">
                    <Tooltip
                        :text="t('admin.inquiries.tooltip_total')"
                        position="top"
                    >
                        <div
                            class="flex-1 cursor-help rounded-lg bg-white p-3 shadow transition-all hover:shadow-md dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('admin.inquiries.total') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100 md:text-2xl"
                            >
                                {{ stats.total || 0 }}
                            </div>
                        </div>
                    </Tooltip>

                    <Tooltip
                        :text="t('admin.inquiries.tooltip_today')"
                        position="top"
                    >
                        <div
                            class="flex-1 cursor-help rounded-lg bg-white p-3 shadow transition-all hover:shadow-md dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('admin.inquiries.today') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-indigo-600 dark:text-indigo-400 md:text-2xl"
                            >
                                {{ stats.today || 0 }}
                            </div>
                        </div>
                    </Tooltip>

                    <Tooltip
                        :text="t('admin.inquiries.tooltip_this_week')"
                        position="top"
                    >
                        <div
                            class="flex-1 cursor-help rounded-lg bg-white p-3 shadow transition-all hover:shadow-md dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('admin.inquiries.this_week') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-green-600 dark:text-green-400 md:text-2xl"
                            >
                                {{ stats.this_week || 0 }}
                            </div>
                        </div>
                    </Tooltip>

                    <Tooltip
                        :text="t('admin.inquiries.tooltip_this_month')"
                        position="top"
                    >
                        <div
                            class="flex-1 cursor-help rounded-lg bg-white p-3 shadow transition-all hover:shadow-md dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('admin.inquiries.this_month') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-blue-600 dark:text-blue-400 md:text-2xl"
                            >
                                {{ stats.this_month || 0 }}
                            </div>
                        </div>
                    </Tooltip>
                </div>

                <!-- Search and Export -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:justify-between"
                >
                    <div class="w-full sm:w-96">
                        <SearchInput
                            v-model="search"
                            :placeholder="
                                t('admin.inquiries.search_placeholder')
                            "
                            route-name="admin.inquiries.index"
                        />
                    </div>
                    <PrimaryButton @click="exportInquiries">
                        <svg
                            class="-ms-1 me-2 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            />
                        </svg>
                        {{ t('admin.inquiries.export_button') }}
                    </PrimaryButton>
                </div>

                <!-- Inquiries Table -->
                <div
                    class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead>
                                <tr>
                                    <SortableHeader
                                        field="email"
                                        :label="t('admin.inquiries.email')"
                                        :current-sort="filters.sort"
                                        :current-direction="filters.direction"
                                    />
                                    <th
                                        class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                    >
                                        {{ t('admin.inquiries.ip_address') }}
                                    </th>
                                    <th
                                        class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                    >
                                        {{ t('admin.inquiries.user_agent') }}
                                    </th>
                                    <SortableHeader
                                        field="created_at"
                                        :label="t('admin.inquiries.created_at')"
                                        :current-sort="filters.sort"
                                        :current-direction="filters.direction"
                                    />
                                    <th
                                        class="bg-gray-50 px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                    >
                                        {{ t('admin.inquiries.delete') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"
                            >
                                <tr
                                    v-for="inquiry in inquiries.data"
                                    :key="inquiry.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ inquiry.email }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ inquiry.ip_address || 'N/A' }}
                                    </td>
                                    <td
                                        class="max-w-xs px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                        :title="inquiry.user_agent"
                                    >
                                        {{ truncate(inquiry.user_agent, 60) }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ formatDate(inquiry.created_at) }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-sm"
                                    >
                                        <DangerButton
                                            :disabled="deleting === inquiry.id"
                                            class="text-xs"
                                            @click="deleteInquiry(inquiry.id)"
                                        >
                                            <span
                                                v-if="deleting === inquiry.id"
                                            >
                                                {{ t('admin.loading') }}
                                            </span>
                                            <span v-else>
                                                {{
                                                    t('admin.inquiries.delete')
                                                }}
                                            </span>
                                        </DangerButton>
                                    </td>
                                </tr>
                                <tr
                                    v-if="inquiries.data.length === 0"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        colspan="5"
                                        class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ t('admin.inquiries.no_results') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <Pagination :links="inquiries.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
