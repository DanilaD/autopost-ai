<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const page = usePage()
const post = computed(() => page.props.post)
</script>

<template>
    <Head :title="post?.title || t('posts.title')" />
    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-900 dark:text-gray-100"
            >
                {{ post?.title || t('posts.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                >
                    <p
                        class="text-gray-700 dark:text-gray-300 whitespace-pre-line"
                    >
                        {{ post?.caption }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
