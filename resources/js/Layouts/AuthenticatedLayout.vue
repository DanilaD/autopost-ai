<script setup>
import { ref, onMounted } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import ToastContainer from '@/Components/ToastContainer.vue'
import LanguageSelector from '@/Components/LanguageSelector.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
import TimezoneIndicator from '@/Components/TimezoneIndicator.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const showingNavigationDropdown = ref(false)
const page = usePage()
const toast = useToast()

// Show toast from session flash data
onMounted(() => {
    const toastData = page.props.toast
    if (toastData) {
        toast.addToast(toastData.message, toastData.type || 'success')
    }
})
</script>

<template>
    <div>
        <!-- Toast Notifications -->
        <ToastContainer />

        <!-- Impersonation Banner -->
        <div
            v-if="$page.props.impersonating"
            class="bg-yellow-500 px-4 py-3 text-center text-sm font-medium text-white"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div>
                    🎭 {{ t('admin.impersonating') }}:
                    {{ $page.props.auth.user.name }} ({{
                        $page.props.auth.user.email
                    }})
                </div>
                <Link
                    :href="route('admin.impersonate.stop')"
                    method="post"
                    as="button"
                    class="rounded bg-white px-3 py-1 text-sm font-medium text-yellow-700 hover:bg-gray-100"
                >
                    {{ t('admin.stop_impersonation') }}
                </Link>
            </div>
        </div>

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    {{ t('menu.dashboard') }}
                                </NavLink>

                                <NavLink
                                    :href="route('posts.index')"
                                    :active="route().current('posts.*')"
                                >
                                    {{ t('menu.posts') }}
                                </NavLink>

                                <!-- Admin Navigation -->
                                <template
                                    v-if="$page.props.auth.user?.is_admin"
                                >
                                    <NavLink
                                        :href="route('admin.inquiries.index')"
                                        :active="
                                            route().current('admin.inquiries.*')
                                        "
                                    >
                                        {{ t('menu.inquiries') }}
                                    </NavLink>

                                    <NavLink
                                        :href="route('admin.users.index')"
                                        :active="
                                            route().current('admin.users.*')
                                        "
                                    >
                                        {{ t('menu.users') }}
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div
                            class="hidden sm:ms-6 sm:flex sm:items-center sm:space-x-3"
                        >
                            <!-- Theme Toggle -->
                            <ThemeToggle />

                            <!-- Language Selector -->
                            <LanguageSelector />

                            <!-- Timezone Indicator -->
                            <TimezoneIndicator />

                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-600 transition duration-150 ease-in-out hover:text-gray-800 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:hover:text-gray-200"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            {{ t('menu.profile') }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            {{ t('menu.logout') }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div
                            class="-me-2 flex items-center space-x-2 sm:hidden"
                        >
                            <!-- Mobile Theme Toggle -->
                            <ThemeToggle />

                            <button
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-gray-200 dark:focus:bg-gray-700"
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            {{ t('menu.dashboard') }}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('posts.index')"
                            :active="route().current('posts.*')"
                        >
                            {{ t('menu.posts') }}
                        </ResponsiveNavLink>

                        <!-- Admin Navigation (Mobile) -->
                        <template v-if="$page.props.auth.user?.is_admin">
                            <ResponsiveNavLink
                                :href="route('admin.inquiries.index')"
                                :active="route().current('admin.inquiries.*')"
                            >
                                {{ t('menu.inquiries') }}
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                :href="route('admin.users.index')"
                                :active="route().current('admin.users.*')"
                            >
                                {{ t('menu.users') }}
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-700"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <!-- Mobile Timezone Display -->
                        <div class="mt-3 px-4">
                            <TimezoneIndicator />
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                {{ t('menu.profile') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                {{ t('menu.logout') }}
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                v-if="$slots.header"
                class="bg-white shadow dark:bg-gray-800 dark:shadow-gray-900/50"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
