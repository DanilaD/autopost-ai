<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, usePage, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, tm } = useI18n()
const page = usePage()
const user = computed(() => page.props.auth.user)

// Get stats from props
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            instagram_accounts: 0,
            scheduled_posts: 0,
            wallet_balance: 0,
        }),
    },
})

// Get current time for greeting
const greeting = computed(() => {
    const hour = new Date().getHours()
    if (hour < 12) return t('dashboard.greeting_morning')
    if (hour < 18) return t('dashboard.greeting_afternoon')
    return t('dashboard.greeting_evening')
})

// Get a random welcome message from the array
// Uses Math.random() to select a different message each time the component loads
// tm() returns the raw translation message (array in this case), while t() returns formatted string
const welcomeMessage = computed(() => {
    const messages = tm('dashboard.welcome_messages')

    if (Array.isArray(messages) && messages.length > 0) {
        const randomIndex = Math.floor(Math.random() * messages.length)
        return messages[randomIndex]
    }

    // Fallback to first message if something goes wrong
    return "Welcome to Autopost AI. Let's make something amazing today."
})
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-900 dark:text-gray-100"
            >
                {{ t('dashboard.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Welcome Card -->
                <div
                    class="overflow-hidden rounded-md shadow-lg bg-indigo-600 dark:bg-indigo-500"
                >
                    <div class="p-8">
                        <h3
                            class="text-2xl font-bold text-white dark:text-white"
                        >
                            {{ greeting }}, {{ user.name }}! 👋
                        </h3>
                        <p
                            class="mt-2 text-indigo-100 opacity-90 dark:text-indigo-200"
                        >
                            {{ welcomeMessage }}
                        </p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <!-- Instagram Accounts -->
                    <div
                        class="overflow-hidden rounded-md bg-white shadow transition-shadow hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 rounded-md bg-indigo-600 p-3 dark:bg-indigo-500"
                                >
                                    <svg
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="truncate text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                t(
                                                    'dashboard.instagram_accounts'
                                                )
                                            }}
                                        </dt>
                                        <dd>
                                            <div
                                                class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{
                                                    props.stats
                                                        .instagram_accounts
                                                }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Posts -->
                    <div
                        class="overflow-hidden rounded-md bg-white shadow transition-shadow hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 rounded-md bg-green-600 p-3 dark:bg-green-500"
                                >
                                    <svg
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="truncate text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{ t('dashboard.scheduled_posts') }}
                                        </dt>
                                        <dd>
                                            <div
                                                class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{
                                                    props.stats.scheduled_posts
                                                }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Credits -->
                    <div
                        class="overflow-hidden rounded-md bg-white shadow transition-shadow hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 rounded-md bg-yellow-500 p-3 dark:bg-yellow-500"
                                >
                                    <svg
                                        class="h-6 w-6 text-black"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="truncate text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{ t('dashboard.wallet_balance') }}
                                        </dt>
                                        <dd>
                                            <div
                                                class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                ${{
                                                    (
                                                        props.stats
                                                            .wallet_balance /
                                                        100
                                                    ).toFixed(2)
                                                }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Cards -->
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Connect Instagram -->
                    <div
                        class="overflow-hidden rounded-md bg-white shadow transition-shadow hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <div
                                    class="flex-shrink-0 rounded-lg bg-pink-600 p-3 dark:bg-pink-500"
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
                                <div class="flex-1">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        {{ t('dashboard.connect_instagram') }}
                                    </h3>
                                    <p
                                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            t(
                                                'dashboard.connect_instagram_desc'
                                            )
                                        }}
                                    </p>
                                    <Link
                                        :href="route('instagram.index')"
                                        class="mt-4 inline-flex items-center rounded-md border border-transparent bg-pink-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out duration-150 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                    >
                                        {{ t('dashboard.connect_now') }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create Post -->
                    <div
                        class="overflow-hidden rounded-md bg-white shadow transition-shadow hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <div
                                    class="flex-shrink-0 rounded-lg bg-blue-600 p-3 dark:bg-blue-500"
                                >
                                    <svg
                                        class="h-8 w-8 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                        />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        {{ t('dashboard.actions.create_post') }}
                                    </h3>
                                    <p
                                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            t(
                                                'dashboard.actions.create_post_desc'
                                            )
                                        }}
                                    </p>
                                    <Link
                                        :href="route('posts.create')"
                                        class="mt-4 inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out duration-150 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                    >
                                        {{ t('dashboard.actions.create_post') }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    class="overflow-hidden rounded-lg bg-white shadow transition-shadow hover:shadow-md dark:bg-gray-800"
                >
                    <div class="p-12 text-center">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <h3
                            class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ t('dashboard.empty_state.no_posts') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ t('dashboard.empty_state.get_started') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
