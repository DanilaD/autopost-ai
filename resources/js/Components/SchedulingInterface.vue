<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ t('posts.scheduling') }}
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ t('posts.scheduling_description') }}
            </p>
        </div>

        <!-- Publishing Options -->
        <div class="space-y-4">
            <!-- Draft (No Publish) - Default -->
            <div class="flex items-center">
                <input
                    id="draft"
                    v-model="publishMode"
                    type="radio"
                    value="draft"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                />
                <label
                    for="draft"
                    class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ t('posts.save_draft') }}
                </label>
            </div>

            <!-- Publish Immediately -->
            <div class="flex items-center">
                <input
                    id="publish_now"
                    v-model="publishMode"
                    type="radio"
                    value="now"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                />
                <label
                    for="publish_now"
                    class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ t('posts.publish_immediately') }}
                </label>
            </div>

            <!-- Schedule for Later -->
            <div class="flex items-center">
                <input
                    id="schedule"
                    v-model="publishMode"
                    type="radio"
                    value="schedule"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600"
                />
                <label
                    for="schedule"
                    class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ t('posts.schedule_for_later') }}
                </label>
            </div>
        </div>

        <!-- Scheduling Interface -->
        <div v-if="publishMode === 'schedule'" class="space-y-6">
            <!-- Quick Select Options -->
            <div>
                <h4
                    class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3"
                >
                    {{ t('posts.quick_select') }}
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        v-for="option in quickSelectOptions"
                        :key="option.value"
                        type="button"
                        :class="[
                            'px-4 py-3 text-center text-sm border rounded-md transition-colors',
                            selectedQuickOption === option.value
                                ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-400'
                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                        ]"
                        @click="selectQuickOption(option)"
                    >
                        <span class="font-medium">{{ option.label }}</span>
                    </button>
                </div>
            </div>

            <!-- Custom Date & Time Selection -->
            <div>
                <h4
                    class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3"
                >
                    {{ t('posts.custom_schedule') }}
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date Picker -->
                    <div>
                        <label
                            :for="`custom-date-${id}`"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            {{ t('posts.select_date') }}
                        </label>
                        <input
                            :id="`custom-date-${id}`"
                            ref="customDateInput"
                            type="date"
                            :placeholder="t('posts.date_placeholder')"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-100"
                            :min="minDateString"
                            @change="onCustomDateChange"
                            @input="onCustomDateChange"
                        />
                    </div>

                    <!-- Time Picker -->
                    <div>
                        <label
                            :for="`custom-time-${id}`"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            {{ t('posts.select_time') }}
                        </label>
                        <input
                            :id="`custom-time-${id}`"
                            ref="customTimeInput"
                            type="time"
                            :placeholder="t('posts.time_placeholder')"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-100"
                            @change="onCustomTimeChange"
                            @input="onCustomTimeChange"
                        />
                    </div>
                </div>
            </div>

            <!-- Selected Schedule Display -->
            <div
                v-if="selectedDateTime"
                class="p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-md"
            >
                <div class="flex items-center space-x-3">
                    <svg
                        class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
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
                    <div>
                        <p
                            class="text-sm font-medium text-indigo-800 dark:text-indigo-200"
                        >
                            {{ t('posts.scheduled_for') }}
                        </p>
                        <p class="text-sm text-indigo-700 dark:text-indigo-300">
                            {{ formattedDateTime }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    modelValue: {
        type: [Date, String, null],
        default: null,
    },
    minDate: {
        type: Date,
        default: () => new Date(),
    },
    timezone: {
        type: String,
        default: 'UTC',
    },
})

const emit = defineEmits(['update:modelValue'])

const id = Math.random().toString(36).substr(2, 9)
const customDateInput = ref(null)
const customTimeInput = ref(null)
const selectedDateTime = ref(null)
const publishMode = ref('draft') // Default to draft
const selectedQuickOption = ref(null)

// Quick select options
const quickSelectOptions = computed(() => {
    const now = new Date()
    const today = new Date(now)
    const tomorrow = new Date(now)
    tomorrow.setDate(tomorrow.getDate() + 1)
    const nextWeek = new Date(now)
    nextWeek.setDate(nextWeek.getDate() + 7)
    const nextMonth = new Date(now)
    nextMonth.setMonth(nextMonth.getMonth() + 1)

    return [
        {
            label: t('posts.now'),
            value: 'now',
            date: new Date(now.getTime()), // Create new instance to avoid reference issues
        },
        {
            label: t('posts.today_1h'),
            value: 'today_1h',
            date: addHours(new Date(today), 1),
        },
        {
            label: t('posts.today_3h'),
            value: 'today_3h',
            date: addHours(new Date(today), 3),
        },
        {
            label: t('posts.today_6h'),
            value: 'today_6h',
            date: addHours(new Date(today), 6),
        },
        {
            label: t('posts.tomorrow_9am'),
            value: 'tomorrow_9am',
            date: setTime(new Date(tomorrow), 9, 0),
        },
        {
            label: t('posts.tomorrow_6pm'),
            value: 'tomorrow_6pm',
            date: setTime(new Date(tomorrow), 18, 0),
        },
        {
            label: t('posts.next_week'),
            value: 'next_week',
            date: setTime(new Date(nextWeek), 9, 0),
        },
        {
            label: t('posts.next_month'),
            value: 'next_month',
            date: setTime(new Date(nextMonth), 9, 0),
        },
    ]
})

const formattedDateTime = computed(() => {
    if (!selectedDateTime.value) return ''

    const date = new Date(selectedDateTime.value)
    return date.toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: props.timezone,
    })
})

// Computed property for min date string
const minDateString = computed(() => {
    return props.minDate.toISOString().split('T')[0]
})

// Helper functions
const addHours = (date, hours) => {
    const newDate = new Date(date)
    newDate.setHours(newDate.getHours() + hours)
    return newDate
}

const setTime = (date, hours, minutes) => {
    const newDate = new Date(date)
    newDate.setHours(hours, minutes, 0, 0)
    return newDate
}

const selectQuickOption = (option) => {
    selectedQuickOption.value = option.value
    selectedDateTime.value = option.date
    emit('update:modelValue', option.date)
    updateCustomInputs(option.date)
}

// Event handlers for native HTML5 inputs
const onCustomDateChange = (event) => {
    const selectedDate = event.target.value
    if (!selectedDate) return

    const currentTime = selectedDateTime.value
        ? new Date(selectedDateTime.value)
        : new Date()

    // Create new date with selected date and current time
    // Use local timezone methods for consistent display
    const [year, month, day] = selectedDate.split('-')
    const newDateTime = new Date(
        parseInt(year),
        parseInt(month) - 1, // Month is 0-indexed
        parseInt(day),
        currentTime.getHours(),
        currentTime.getMinutes(),
        currentTime.getSeconds()
    )

    selectedDateTime.value = newDateTime
    emit('update:modelValue', newDateTime)
    selectedQuickOption.value = null // Clear quick selection
}

const onCustomTimeChange = (event) => {
    const selectedTime = event.target.value
    if (!selectedTime) return

    const currentDate = selectedDateTime.value
        ? new Date(selectedDateTime.value)
        : new Date()

    // Create new date with current date and selected time
    // Use local timezone methods for consistent display
    const [hours, minutes] = selectedTime.split(':')
    const newDateTime = new Date(
        currentDate.getFullYear(),
        currentDate.getMonth(),
        currentDate.getDate(),
        parseInt(hours),
        parseInt(minutes),
        0
    )

    selectedDateTime.value = newDateTime
    emit('update:modelValue', newDateTime)
    selectedQuickOption.value = null // Clear quick selection
}

const updateCustomInputs = (date) => {
    if (!date) return

    const dateObj = new Date(date)

    // Update custom date input (native HTML5)
    if (customDateInput.value) {
        const dateStr = dateObj.toISOString().split('T')[0] // Format as YYYY-MM-DD
        customDateInput.value.value = dateStr
    }

    // Update custom time input (native HTML5)
    if (customTimeInput.value) {
        // Format as HH:MM in local timezone
        const hours = dateObj.getHours().toString().padStart(2, '0')
        const minutes = dateObj.getMinutes().toString().padStart(2, '0')
        const timeStr = `${hours}:${minutes}`
        customTimeInput.value.value = timeStr
    }
}

// Watch for prop changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            // Convert UTC time from database to local time for display
            const utcDate = new Date(newValue)
            selectedDateTime.value = utcDate
            updateCustomInputs(utcDate)

            // Set publish mode to schedule if there's a scheduled time
            if (publishMode.value === 'draft') {
                publishMode.value = 'schedule'
            }

            // Try to match with quick select options
            const quickOption = quickSelectOptions.value.find((option) => {
                const optionTime = option.date.getTime()
                const modelTime = utcDate.getTime()
                // Allow 1 minute tolerance for matching
                return Math.abs(optionTime - modelTime) < 60000
            })

            if (quickOption) {
                selectedQuickOption.value = quickOption.value
            } else {
                selectedQuickOption.value = null
            }
        } else {
            selectedDateTime.value = null
            selectedQuickOption.value = null
        }
    },
    { immediate: true }
)

// Watch publish mode changes
watch(publishMode, (newMode) => {
    if (newMode === 'draft') {
        emit('update:modelValue', null)
        selectedDateTime.value = null
        selectedQuickOption.value = null
    } else if (newMode === 'now') {
        const currentTime = new Date()
        emit('update:modelValue', currentTime)
        selectedDateTime.value = currentTime
        selectedQuickOption.value = 'now'
    }
})

onMounted(() => {
    // Set initial value if provided
    if (props.modelValue) {
        selectedDateTime.value = new Date(props.modelValue)
        updateCustomInputs(props.modelValue)
        publishMode.value = 'schedule'

        // Try to match with quick select options
        const quickOption = quickSelectOptions.value.find((option) => {
            const optionTime = option.date.getTime()
            const modelTime = new Date(props.modelValue).getTime()
            // Allow 1 minute tolerance for matching
            return Math.abs(optionTime - modelTime) < 60000
        })

        if (quickOption) {
            selectedQuickOption.value = quickOption.value
        }
    }
})

onUnmounted(() => {
    // Cleanup is not needed for native HTML5 inputs
})
</script>
