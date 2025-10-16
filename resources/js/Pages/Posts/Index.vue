<template>
    <Head :title="t('posts.title')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-900 dark:text-gray-100"
                >
                    {{ t('posts.title') }}
                </h2>
                <Link
                    :href="route('posts.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    {{ t('posts.create_post') }}
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="mb-6">
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6"
                    >
                        <!-- Total Posts -->
                        <div
                            class="flex-1 rounded-md bg-white p-3 shadow dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('posts.stats.total_posts') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100 md:text-2xl"
                            >
                                {{ stats.total || 0 }}
                            </div>
                        </div>

                        <!-- Drafts -->
                        <div
                            class="flex-1 rounded-md bg-white p-3 shadow dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('posts.status.draft') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-gray-600 dark:text-gray-400 md:text-2xl"
                            >
                                {{ stats.drafts || 0 }}
                            </div>
                        </div>

                        <!-- Scheduled -->
                        <div
                            class="flex-1 rounded-md bg-white p-3 shadow dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('posts.status.scheduled') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-blue-600 dark:text-blue-400 md:text-2xl"
                            >
                                {{ stats.scheduled || 0 }}
                            </div>
                        </div>

                        <!-- Publishing -->
                        <div
                            class="flex-1 rounded-md bg-white p-3 shadow dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('posts.status.publishing') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-yellow-600 dark:text-yellow-400 md:text-2xl"
                            >
                                {{ stats.publishing || 0 }}
                            </div>
                        </div>

                        <!-- Published -->
                        <div
                            class="flex-1 rounded-md bg-white p-3 shadow dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('posts.status.published') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-green-600 dark:text-green-400 md:text-2xl"
                            >
                                {{ stats.published || 0 }}
                            </div>
                        </div>

                        <!-- Failed -->
                        <div
                            class="flex-1 rounded-md bg-white p-3 shadow dark:bg-gray-800 md:p-4"
                        >
                            <div
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 md:text-sm"
                            >
                                {{ t('posts.status.failed') }}
                            </div>
                            <div
                                class="mt-1 text-xl font-bold text-red-600 dark:text-red-400 md:text-2xl"
                            >
                                {{ stats.failed || 0 }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6">
                    <div class="flex flex-wrap gap-4">
                        <select
                            v-model="filters.status"
                            class="rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            @change="applyFilters"
                        >
                            <option value="">
                                {{ t('posts.all_statuses') }}
                            </option>
                            <option value="draft">
                                {{ t('posts.status.draft') }}
                            </option>
                            <option value="scheduled">
                                {{ t('posts.status.scheduled') }}
                            </option>
                            <option value="publishing">
                                {{ t('posts.status.publishing') }}
                            </option>
                            <option value="published">
                                {{ t('posts.status.published') }}
                            </option>
                            <option value="failed">
                                {{ t('posts.status.failed') }}
                            </option>
                        </select>

                        <select
                            v-model="filters.type"
                            class="rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            @change="applyFilters"
                        >
                            <option value="">
                                {{ t('posts.all_types') }}
                            </option>
                            <option value="feed">
                                {{ t('posts.type.feed') }}
                            </option>
                            <option value="reel">
                                {{ t('posts.type.reel') }}
                            </option>
                            <option value="story">
                                {{ t('posts.type.story') }}
                            </option>
                            <option value="carousel">
                                {{ t('posts.type.carousel') }}
                            </option>
                        </select>

                        <input
                            v-model="filters.search"
                            type="text"
                            :placeholder="t('posts.search_posts')"
                            class="rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            @input="debouncedSearch"
                        />
                    </div>
                </div>

                <!-- Posts Table -->
                <div
                    v-if="posts.length > 0"
                    class="overflow-hidden bg-white shadow dark:bg-gray-800 sm:rounded-lg"
                >
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    {{ t('posts.title') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    {{ t('posts.caption') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    {{ t('posts.media_count') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    {{ t('posts.status_label') }}
                                </th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    {{ t('posts.scheduled_at') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    {{ t('posts.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="post in posts"
                                :key="post.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <!-- Title -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ post.title || t('posts.untitled') }}
                                    </div>
                                </td>

                                <!-- Caption (truncated) -->
                                <td class="px-6 py-4">
                                    <div
                                        class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate"
                                    >
                                        {{
                                            post.caption ||
                                            t('posts.no_caption')
                                        }}
                                    </div>
                                </td>

                                <!-- Media Count -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        <span
                                            v-if="
                                                post.media &&
                                                post.media.length > 0
                                            "
                                            class="flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    v-if="
                                                        getMediaTypeCount(
                                                            post.media,
                                                            'image'
                                                        ) > 0
                                                    "
                                                    fill-rule="evenodd"
                                                    d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                                    clip-rule="evenodd"
                                                />
                                                <path
                                                    v-else
                                                    fill-rule="evenodd"
                                                    d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm2 2a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            {{
                                                getMediaTypeCount(
                                                    post.media,
                                                    'image'
                                                )
                                            }}
                                            {{ t('posts.images') }}
                                            <span
                                                v-if="
                                                    getMediaTypeCount(
                                                        post.media,
                                                        'video'
                                                    ) > 0
                                                "
                                                class="ml-2"
                                            >
                                                {{
                                                    getMediaTypeCount(
                                                        post.media,
                                                        'video'
                                                    )
                                                }}
                                                {{ t('posts.videos') }}
                                            </span>
                                        </span>
                                        <span v-else class="text-gray-400">
                                            {{ t('posts.no_media') }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="getStatusClass(post.status)"
                                    >
                                        {{ t(`posts.status.${post.status}`) }}
                                    </span>
                                </td>

                                <!-- Scheduled At -->
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{
                                        post.scheduled_at
                                            ? formatDateTime(post.scheduled_at)
                                            : t('posts.not_scheduled')
                                    }}
                                </td>

                                <!-- Actions -->
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
                                >
                                    <div
                                        class="flex items-center justify-center space-x-3"
                                    >
                                        <button
                                            class="text-red-600 hover:text-red-500 dark:text-red-400"
                                            :title="t('posts.delete')"
                                            @click="deletePost(post.id)"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                        <Link
                                            :href="route('posts.edit', post.id)"
                                            class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                                            :title="t('posts.edit')"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="
                                                route('posts.create', {
                                                    duplicate: post.id,
                                                })
                                            "
                                            class="text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100"
                                            :title="t('posts.duplicate')"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                                />
                                            </svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center">
                    <div
                        class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600"
                    >
                        <svg
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <h3
                        class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                    >
                        {{ t('posts.no_posts') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('posts.no_posts_description') }}
                    </p>
                    <div class="mt-6">
                        <Link
                            :href="route('posts.create')"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ t('posts.create_post') }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useSweetAlert } from '@/composables/useSweetAlert'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useI18n } from 'vue-i18n'
import { useToast } from '@/composables/useToast'
import { debounce } from 'lodash-es'

const { t } = useI18n()
const toast = useToast()
const swal = useSweetAlert()

const props = defineProps({
    posts: {
        type: Array,
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

const filters = ref({
    status: props.filters.status || '',
    type: props.filters.type || '',
    search: props.filters.search || '',
})

const debouncedSearch = debounce(() => {
    applyFilters()
}, 300)

const applyFilters = () => {
    router.get(route('posts.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        scheduled:
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        publishing:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        published:
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    }
    return classes[status] || classes.draft
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    })
}

const getMediaTypeCount = (media, type) => {
    if (!media || !Array.isArray(media)) return 0
    return media.filter((item) => item.type === type).length
}

const deletePost = async (postId) => {
    const result = await swal.confirm(t('posts.confirm_delete'), '', {
        confirmButtonText: t('admin.users.confirm'),
        cancelButtonText: t('admin.users.cancel'),
    })

    if (!result.isConfirmed) return

    router.delete(route('posts.destroy', postId), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(t('posts.deleted_successfully'))
        },
        onError: () => {
            toast.error(t('posts.delete_failed'))
        },
    })
}
</script>
