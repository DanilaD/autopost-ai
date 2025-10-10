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
            class="inline-flex items-center gap-x-2 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            @click="toggleDropdown"
        >
            <svg
                class="h-5 w-5 text-gray-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"
                />
            </svg>
            <span>{{ currentLanguage.nativeName }}</span>
            <svg
                class="-mr-1 h-5 w-5 text-gray-400"
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
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="showDropdown"
                class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                role="menu"
                aria-orientation="vertical"
            >
                <div class="py-1" role="none">
                    <button
                        v-for="language in languages"
                        :key="language.code"
                        type="button"
                        :class="[
                            'flex w-full items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900',
                            currentLocale === language.code
                                ? 'bg-gray-50 font-semibold'
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
                            class="h-5 w-5 text-indigo-600"
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
