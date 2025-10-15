<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const currentLocale = computed(() => page.props.locale || 'en')
const showDropdown = ref(false)

const languages = [
    { code: 'en', name: 'English', nativeName: 'English' },
    { code: 'ru', name: 'Russian', nativeName: 'Русский' },
    { code: 'es', name: 'Spanish', nativeName: 'Español' },
]

const currentLanguage = computed(() => {
    return (
        languages.find((lang) => lang.code === currentLocale.value) ||
        languages[0]
    )
})

const changeLanguage = (locale) => {
    // Store in LocalStorage for guests (persists across sessions)
    if (typeof window !== 'undefined' && window.localStorage) {
        window.localStorage.setItem('preferred_locale', locale)
    }

    router.post(
        route('locale.change'),
        { locale },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                showDropdown.value = false
                // Reload page to apply new translations
                window.location.reload()
            },
        }
    )
}

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value
}

// Close dropdown when clicking outside
const closeDropdown = (event) => {
    if (!event.target.closest('.language-selector')) {
        showDropdown.value = false
    }
}

// Add click listener when component mounts
if (typeof window !== 'undefined') {
    document.addEventListener('click', closeDropdown)
}
</script>

<template>
    <div class="language-selector relative">
        <button
            type="button"
            class="inline-flex items-center gap-x-1.5 rounded-sm bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow ring-1 ring-inset ring-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700"
            @click="toggleDropdown"
        >
            <span>{{ currentLanguage.nativeName }}</span>
            <svg
                class="h-4 w-4 text-gray-500 dark:text-gray-400"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <transition
            enter-active-class="transition ease-out duration-medium2"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-medium1"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="showDropdown"
                class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow ring-1 ring-gray-200 focus:outline-none dark:bg-gray-800 dark:ring-gray-700"
                role="menu"
                aria-orientation="vertical"
            >
                <div class="py-1" role="none">
                    <button
                        v-for="language in languages"
                        :key="language.code"
                        type="button"
                        :class="[
                            'flex w-full items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-all duration-150 dark:text-gray-200 dark:hover:bg-gray-700',
                            currentLocale === language.code
                                ? 'font-semibold text-indigo-600 dark:text-indigo-400'
                                : '',
                        ]"
                        @click="changeLanguage(language.code)"
                    >
                        <span class="flex-1 text-left">{{
                            language.nativeName
                        }}</span>
                        <!-- Checkmark for current language -->
                        <svg
                            v-if="currentLocale === language.code"
                            class="h-5 w-5 text-md-primary"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Ensure dropdown is above other elements */
.language-selector {
    z-index: 50;
}
</style>
