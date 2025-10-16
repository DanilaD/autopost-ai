<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const form = useForm({
    name: '',
})

const submit = () => {
    form.post(route('companies.store'))
}
</script>

<template>
    <Head title="Create Company" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ t('company.create.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Company Creation Form -->
                        <div class="max-w-md mx-auto">
                            <div class="mb-8 text-center">
                                <div
                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900"
                                >
                                    <svg
                                        class="h-8 w-8 text-blue-600 dark:text-blue-400"
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
                                <h3
                                    class="mt-4 text-lg font-medium text-gray-900 dark:text-white"
                                >
                                    {{ t('company.create.heading') }}
                                </h3>
                                <p
                                    class="mt-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ t('company.create.description') }}
                                </p>
                            </div>

                            <form class="space-y-6" @submit.prevent="submit">
                                <div>
                                    <label
                                        for="name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        {{ t('company.create.name_label') }}
                                    </label>
                                    <div class="mt-1">
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            required
                                            :placeholder="
                                                t(
                                                    'company.create.name_placeholder'
                                                )
                                            "
                                            class="block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.name,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.name"
                                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ form.errors.name }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4"
                                >
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg
                                                class="h-5 w-5 text-blue-400"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3
                                                class="text-sm font-medium text-blue-800 dark:text-blue-200"
                                            >
                                                {{
                                                    t(
                                                        'company.create.info_title'
                                                    )
                                                }}
                                            </h3>
                                            <div
                                                class="mt-2 text-sm text-blue-700 dark:text-blue-300"
                                            >
                                                <ul
                                                    class="list-disc list-inside space-y-1"
                                                >
                                                    <li>
                                                        {{
                                                            t(
                                                                'company.create.info_1'
                                                            )
                                                        }}
                                                    </li>
                                                    <li>
                                                        {{
                                                            t(
                                                                'company.create.info_2'
                                                            )
                                                        }}
                                                    </li>
                                                    <li>
                                                        {{
                                                            t(
                                                                'company.create.info_3'
                                                            )
                                                        }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50"
                                    >
                                        <span v-if="form.processing">{{
                                            t('company.create.creating')
                                        }}</span>
                                        <span v-else>{{
                                            t('company.create.create_button')
                                        }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
