<template>
    <div class="space-y-4">
        <!-- Date Picker -->
        <div>
            <label
                :for="`date-${id}`"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
            >
                {{ t('posts.select_date') }}
            </label>
            <input
                :id="`date-${id}`"
                ref="dateInput"
                type="text"
                :placeholder="t('posts.date_placeholder')"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-100"
                readonly
            />
        </div>

        <!-- Time Picker -->
        <div>
            <label
                :for="`time-${id}`"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
            >
                {{ t('posts.select_time') }}
            </label>
            <input
                :id="`time-${id}`"
                ref="timeInput"
                type="text"
                :placeholder="t('posts.time_placeholder')"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-100"
                readonly
            />
        </div>

        <!-- Selected DateTime Display -->
        <div
            v-if="selectedDateTime"
            class="p-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-md"
        >
            <div class="flex items-center space-x-2">
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
                <span
                    class="text-sm font-medium text-indigo-800 dark:text-indigo-200"
                >
                    {{ t('posts.scheduled_for') }}: {{ formattedDateTime }}
                </span>
            </div>
        </div>

        <!-- Quick Time Options -->
        <div class="space-y-2">
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                {{ t('posts.quick_select') }}
            </label>
            <div class="grid grid-cols-2 gap-2">
                <button
                    v-for="option in quickTimeOptions"
                    :key="option.value"
                    type="button"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-100"
                    @click="selectQuickTime(option.value)"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.min.css'

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
    maxDate: {
        type: Date,
        default: null,
    },
    timezone: {
        type: String,
        default: 'UTC',
    },
})

const emit = defineEmits(['update:modelValue'])

const id = Math.random().toString(36).substr(2, 9)
const dateInput = ref(null)
const timeInput = ref(null)
const selectedDateTime = ref(null)

let datePicker = null
let timePicker = null

const quickTimeOptions = computed(() => [
    { label: t('posts.today_6pm'), value: getTodayAt(18) },
    { label: t('posts.tomorrow_9am'), value: getTomorrowAt(9) },
    { label: t('posts.tomorrow_12pm'), value: getTomorrowAt(12) },
    { label: t('posts.tomorrow_6pm'), value: getTomorrowAt(18) },
    { label: t('posts.next_week'), value: getNextWeek() },
    { label: t('posts.next_month'), value: getNextMonth() },
])

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

const getTodayAt = (hour) => {
    const date = new Date()
    date.setHours(hour, 0, 0, 0)
    return date
}

const getTomorrowAt = (hour) => {
    const date = new Date()
    date.setDate(date.getDate() + 1)
    date.setHours(hour, 0, 0, 0)
    return date
}

const getNextWeek = () => {
    const date = new Date()
    date.setDate(date.getDate() + 7)
    date.setHours(9, 0, 0, 0)
    return date
}

const getNextMonth = () => {
    const date = new Date()
    date.setMonth(date.getMonth() + 1)
    date.setHours(9, 0, 0, 0)
    return date
}

const selectQuickTime = (date) => {
    selectedDateTime.value = date
    emit('update:modelValue', date)
    updateInputs(date)
}

const updateInputs = (date) => {
    if (!date) return

    const dateObj = new Date(date)

    // Update date input
    if (datePicker) {
        datePicker.setDate(dateObj, false)
    }

    // Update time input
    if (timePicker) {
        timePicker.setDate(dateObj, false)
    }
}

const initializeDatePicker = () => {
    if (!dateInput.value) return

    datePicker = flatpickr(dateInput.value, {
        dateFormat: 'Y-m-d',
        minDate: props.minDate,
        maxDate: props.maxDate,
        onChange: (selectedDates) => {
            if (selectedDates.length > 0) {
                const selectedDate = selectedDates[0]
                const currentTime = selectedDateTime.value
                    ? new Date(selectedDateTime.value)
                    : new Date()

                // Preserve time, update date
                const newDateTime = new Date(selectedDate)
                newDateTime.setHours(currentTime.getHours())
                newDateTime.setMinutes(currentTime.getMinutes())

                selectedDateTime.value = newDateTime
                emit('update:modelValue', newDateTime)

                // Update time picker
                if (timePicker) {
                    timePicker.setDate(newDateTime, false)
                }
            }
        },
    })
}

const initializeTimePicker = () => {
    if (!timeInput.value) return

    timePicker = flatpickr(timeInput.value, {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        onChange: (selectedDates) => {
            if (selectedDates.length > 0) {
                const selectedTime = selectedDates[0]
                const currentDate = selectedDateTime.value
                    ? new Date(selectedDateTime.value)
                    : new Date()

                // Preserve date, update time
                const newDateTime = new Date(currentDate)
                newDateTime.setHours(selectedTime.getHours())
                newDateTime.setMinutes(selectedTime.getMinutes())

                selectedDateTime.value = newDateTime
                emit('update:modelValue', newDateTime)
            }
        },
    })
}

// Watch for prop changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            selectedDateTime.value = new Date(newValue)
            updateInputs(newValue)
        }
    },
    { immediate: true }
)

onMounted(() => {
    initializeDatePicker()
    initializeTimePicker()

    // Set initial value if provided
    if (props.modelValue) {
        selectedDateTime.value = new Date(props.modelValue)
        updateInputs(props.modelValue)
    }
})

onUnmounted(() => {
    if (datePicker) {
        datePicker.destroy()
    }
    if (timePicker) {
        timePicker.destroy()
    }
})
</script>
