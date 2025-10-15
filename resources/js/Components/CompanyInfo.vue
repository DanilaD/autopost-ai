<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link } from '@inertiajs/vue3'

const { t } = useI18n()

const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
})

// Format role for display
const roleDisplay = computed(() => {
    if (!props.company.stats?.user_role) return null

    const roleMap = {
        admin: t('profile.company.role_admin'),
        user: t('profile.company.role_user'),
        network: t('profile.company.role_network'),
    }

    return (
        roleMap[props.company.stats.user_role] || props.company.stats.user_role
    )
})

// Format member count
const memberCountText = computed(() => {
    const count = props.company.stats?.team_members_count || 0
    return count === 1
        ? t('profile.company.member_singular')
        : t('profile.company.member_plural', { count })
})

// Format Instagram accounts count
const instagramCountText = computed(() => {
    const count = props.company.stats?.instagram_accounts_count || 0
    return count === 1
        ? t('profile.company.instagram_account_singular')
        : t('profile.company.instagram_account_plural', { count })
})
</script>

<template>
    <div
        class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-6 border border-blue-200 dark:border-gray-600"
    >
        <!-- Company Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ company.name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ t('profile.company.member_since') }}
                    {{ company.created_at }}
                </p>
            </div>

            <!-- Company Icon -->
            <div
                class="h-12 w-12 bg-blue-500 rounded-lg flex items-center justify-center"
            >
                <svg
                    class="h-6 w-6 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    />
                </svg>
            </div>
        </div>

        <!-- Role Badge -->
        <div v-if="roleDisplay" class="mb-4">
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
            >
                <svg
                    class="w-3 h-3 mr-1"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                        clip-rule="evenodd"
                    />
                </svg>
                {{ roleDisplay }}
            </span>
        </div>

        <!-- Company Stats -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Team Members -->
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ company.stats?.team_members_count || 0 }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ memberCountText }}
                </div>
            </div>

            <!-- Instagram Accounts -->
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ company.stats?.instagram_accounts_count || 0 }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ instagramCountText }}
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="mt-4 pt-4 border-t border-blue-200 dark:border-gray-600">
            <Link
                :href="route('instagram.index')"
                class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
            >
                <svg
                    class="w-4 h-4 mr-1"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd"
                    />
                </svg>
                {{ t('profile.company.manage_accounts') }}
            </Link>
        </div>
    </div>
</template>
