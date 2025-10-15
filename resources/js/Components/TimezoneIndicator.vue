<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { formatInTimezone } from '@/composables/useTimezone.js'

const { t } = useI18n()
const page = usePage()

// Current time in user's timezone
const currentTime = ref(new Date())
let intervalId = null

// Update time every minute
onMounted(() => {
    intervalId = setInterval(() => {
        currentTime.value = new Date()
    }, 60000) // Update every 60 seconds
})

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId)
    }
})

// Get user's timezone
const userTimezone = computed(() => {
    return page.props.auth.user.timezone || 'UTC'
})

// Get abbreviated timezone (e.g., "EST", "GMT+3")
const timezoneAbbreviation = computed(() => {
    try {
        const date = new Date()
        const formatted = date.toLocaleTimeString('en-US', {
            timeZone: userTimezone.value,
            timeZoneName: 'short',
        })

        // Extract timezone abbreviation (e.g., "3:45:12 PM EST" -> "EST")
        const parts = formatted.split(' ')
        const abbr = parts[parts.length - 1]

        // If abbreviation looks like GMT offset, keep it; otherwise return it as is
        if (abbr && abbr.length <= 10) {
            return abbr
        }

        // Fallback: calculate GMT offset
        return gmtOffset.value.replace('GMT', '')
    } catch (error) {
        return gmtOffset.value.replace('GMT', '')
    }
})

// Get full timezone name for tooltip
const timezoneName = computed(() => {
    // Format: "America/New_York" -> "New York"
    const parts = userTimezone.value.split('/')
    return parts[parts.length - 1].replace(/_/g, ' ')
})

// Get current time formatted
const formattedTime = computed(() => {
    try {
        return formatInTimezone(currentTime.value, userTimezone.value, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        })
    } catch (error) {
        return '12:00 PM'
    }
})

// Get GMT offset
const gmtOffset = computed(() => {
    try {
        const date = currentTime.value

        // Get the timezone offset in minutes
        const utcDate = Date.UTC(
            date.getUTCFullYear(),
            date.getUTCMonth(),
            date.getUTCDate(),
            date.getUTCHours(),
            date.getUTCMinutes()
        )

        const tzString = date.toLocaleString('en-US', {
            timeZone: userTimezone.value,
        })
        const tzDate = new Date(tzString).getTime()

        // Calculate offset in minutes
        const offsetMinutes = Math.round(
            (tzDate - date.getTime()) / (1000 * 60)
        )
        const offsetHours = offsetMinutes / 60

        const sign = offsetHours >= 0 ? '+' : '-'
        const absHours = Math.abs(Math.floor(offsetHours))
        const minutes = Math.abs(offsetMinutes % 60)

        if (minutes === 0) {
            return `GMT${sign}${absHours}`
        } else {
            return `GMT${sign}${absHours}:${minutes.toString().padStart(2, '0')}`
        }
    } catch (error) {
        return 'GMT+0'
    }
})
</script>

<template>
    <div class="relative group">
        <!-- Timezone Badge Button -->
        <Link
            :href="route('profile.edit')"
            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-600 transition duration-150 ease-in-out hover:text-gray-800 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:hover:text-gray-200"
            :title="t('timezone.click_to_change')"
            preserve-scroll
        >
            <!-- Clock Icon -->
            <svg
                class="h-4 w-4 text-gray-500 transition-colors dark:text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>

            <!-- Timezone Abbreviation -->
            <span class="font-semibold ms-px">{{ timezoneAbbreviation }}</span>
        </Link>

        <!-- Tooltip on hover (separate from link) -->
        <div
            class="pointer-events-none absolute left-1/2 top-full z-50 mt-2 hidden w-56 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-3 shadow-lg group-hover:block dark:border-gray-700 dark:bg-gray-800"
        >
            <!-- Arrow -->
            <div
                class="absolute left-1/2 top-0 -translate-x-1/2 -translate-y-full"
            >
                <div
                    class="border-b-8 border-l-8 border-r-8 border-transparent border-b-gray-200 dark:border-b-gray-700"
                />
                <div
                    class="absolute left-1/2 top-0 -translate-x-1/2 border-b-8 border-l-8 border-r-8 border-transparent border-b-white dark:border-b-gray-800"
                    style="margin-top: 1px"
                />
            </div>

            <!-- Tooltip Content -->
            <div class="space-y-1.5 text-center">
                <div
                    class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                >
                    {{ timezoneName }}
                </div>
                <div
                    class="text-base font-bold text-indigo-600 dark:text-indigo-400"
                >
                    {{ formattedTime }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ gmtOffset }}
                </div>
                <div
                    class="mt-2 border-t border-gray-200 pt-1.5 text-xs text-gray-600 dark:border-gray-700 dark:text-gray-300"
                >
                    {{ t('timezone.click_to_change') }}
                </div>
            </div>
        </div>
    </div>
</template>
