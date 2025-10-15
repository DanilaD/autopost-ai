<script setup>
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import SimpleSearchableSelect from '@/Components/SimpleSearchableSelect.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { detectBrowserTimezone } from '@/composables/useTimezone.js'
import { onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    timezones: {
        type: Object,
        required: true,
    },
    commonTimezones: {
        type: Object,
        required: true,
    },
})

const user = usePage().props.auth.user

const form = useForm({
    name: user.name,
    email: user.email,
    timezone: user.timezone || 'UTC',
})

// Convert timezone objects to array format for SimpleSearchableSelect
const timezoneOptions = computed(() => {
    // Check if main timezones prop exists
    if (!props.timezones || typeof props.timezones !== 'object') {
        return []
    }

    let result = []

    // Add common timezones first (if available)
    if (props.commonTimezones && typeof props.commonTimezones === 'object') {
        const commonOptions = Object.entries(props.commonTimezones).map(
            ([value, label]) => ({
                value,
                label: `⭐ ${label}`, // Add star for common timezones
            })
        )
        result = [...commonOptions]
    }

    // Convert all timezones to array format
    const allOptions = Object.entries(props.timezones).map(
        ([value, label]) => ({
            value,
            label,
        })
    )

    // Combine common timezones first, then all others
    result = [...result, ...allOptions]

    return result
})

// If user doesn't have a timezone set, detect it from browser
onMounted(() => {
    if (!user.timezone || user.timezone === 'UTC') {
        const detected = detectBrowserTimezone()
        if (detected && detected !== 'UTC') {
            form.timezone = detected
        }
    }
})
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ t('profile.information.title') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ t('profile.information.description') }}
            </p>

            <div
                class="mt-3 rounded-md border border-indigo-200 bg-indigo-50 p-3 dark:border-indigo-700 dark:bg-indigo-900/20"
            >
                <p class="text-sm text-indigo-800 dark:text-indigo-200">
                    <svg
                        class="w-4 h-4 inline mr-1"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ t('profile.information.timezone_description') }}
                </p>
            </div>
        </header>

        <form
            class="mt-6 space-y-6"
            @submit.prevent="form.patch(route('profile.update'))"
        >
            <div>
                <InputLabel for="name" :value="t('profile.information.name')" />

                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel
                    for="email"
                    :value="t('profile.information.email')"
                />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel
                    for="timezone"
                    :value="t('profile.information.timezone')"
                />

                <SimpleSearchableSelect
                    v-model="form.timezone"
                    :options="timezoneOptions"
                    :placeholder="t('profile.information.timezone')"
                    :search-placeholder="
                        t('profile.information.search_timezone')
                    "
                    :error="form.errors.timezone"
                    class="mt-1"
                />

                <InputError class="mt-2" :message="form.errors.timezone" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ t('profile.information.unverified_email') }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-indigo-600 underline hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-indigo-400 dark:hover:text-indigo-300 dark:focus:ring-offset-gray-900"
                    >
                        {{ t('profile.information.resend_verification') }}
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-md-success"
                >
                    {{ t('profile.information.verification_sent') }}
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    {{ t('profile.information.save') }}
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-md-on-surface-variant"
                    >
                        {{ t('profile.information.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
