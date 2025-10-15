<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-900 dark:text-gray-100"
                >
                    {{ t('posts.create_post') }}
                </h2>
                <Link
                    :href="route('posts.index')"
                    class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    {{ t('posts.back_to_posts') }}
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form class="space-y-8" @submit.prevent="submitForm">
                    <!-- Post Type Selection -->
                    <div
                        class="rounded-md bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ t('posts.select_post_type') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ t('posts.select_post_type_description') }}
                        </p>

                        <div
                            class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <div
                                v-for="type in postTypes"
                                :key="type.value"
                                class="relative cursor-pointer rounded-md border p-4 focus:outline-none"
                                :class="[
                                    form.type === type.value
                                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                                        : 'border-gray-300 hover:border-gray-400 dark:border-gray-600',
                                ]"
                                @click="selectPostType(type.value)"
                            >
                                <input
                                    :id="type.value"
                                    v-model="form.type"
                                    type="radio"
                                    :value="type.value"
                                    class="sr-only"
                                />
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <component
                                            :is="type.icon"
                                            class="h-6 w-6"
                                            :class="[
                                                form.type === type.value
                                                    ? 'text-indigo-600 dark:text-indigo-400'
                                                    : 'text-gray-400',
                                            ]"
                                        />
                                    </div>
                                    <div class="ml-3">
                                        <label
                                            :for="type.value"
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ type.label }}
                                        </label>
                                        <p
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{ type.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instagram Account Selection -->
                    <div
                        class="rounded-md bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ t('posts.select_instagram_account') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{
                                t('posts.select_instagram_account_description')
                            }}
                        </p>

                        <div class="mt-4">
                            <select
                                v-model="form.instagram_account_id"
                                class="w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">
                                    {{ t('posts.select_account') }}
                                </option>
                                <option
                                    v-for="account in instagramAccounts"
                                    :key="account.id"
                                    :value="account.id"
                                >
                                    {{ account.username }} ({{
                                        account.display_name
                                    }})
                                </option>
                            </select>
                            <p
                                v-if="errors?.instagram_account_id"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ errors.instagram_account_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Post Details -->
                    <div
                        class="rounded-md bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ t('posts.post_details') }}
                        </h3>

                        <div class="mt-4 space-y-4">
                            <!-- Title -->
                            <div>
                                <label
                                    for="title"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('posts.title') }}
                                    <span
                                        class="text-gray-500 dark:text-gray-400"
                                        >({{ t('posts.optional') }})</span
                                    >
                                </label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    :placeholder="t('posts.title_placeholder')"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                />
                            </div>

                            <!-- Caption -->
                            <div>
                                <label
                                    for="caption"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('posts.caption') }}
                                </label>
                                <textarea
                                    id="caption"
                                    v-model="form.caption"
                                    rows="4"
                                    :placeholder="
                                        t('posts.caption_placeholder')
                                    "
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                />
                                <p
                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ form.caption.length }}/2200
                                    {{ t('posts.characters') }}
                                </p>
                                <p
                                    v-if="errors?.caption"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ errors.caption }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Media Upload -->
                    <div
                        class="rounded-md bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ t('posts.media_upload') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ t('posts.media_upload_description') }}
                        </p>

                        <div class="mt-4">
                            <MediaUpload
                                v-model="form.media"
                                :max-files="maxMediaFiles"
                                :allowed-types="allowedMediaTypes"
                                @update:model-value="updateMedia"
                            />
                            <p
                                v-if="errors?.media"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ errors.media }}
                            </p>
                            <p
                                v-if="errors?.['media.0.file']"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ errors['media.0.file'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Scheduling -->
                    <div
                        class="rounded-md bg-white p-6 shadow dark:bg-gray-800"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ t('posts.scheduling') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ t('posts.scheduling_description') }}
                        </p>

                        <div class="mt-4">
                            <div class="flex items-center">
                                <input
                                    id="publish_now"
                                    v-model="publishMode"
                                    type="radio"
                                    value="now"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                />
                                <label
                                    for="publish_now"
                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('posts.publish_immediately') }}
                                </label>
                            </div>

                            <div class="mt-2 flex items-center">
                                <input
                                    id="schedule"
                                    v-model="publishMode"
                                    type="radio"
                                    value="schedule"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                />
                                <label
                                    for="schedule"
                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('posts.schedule_for_later') }}
                                </label>
                            </div>

                            <div v-if="publishMode === 'schedule'" class="mt-4">
                                <DateTimePicker
                                    v-model="form.scheduled_at"
                                    :min-date="minDateTime"
                                    :timezone="userTimezone"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-4">
                        <Link
                            :href="route('posts.index')"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            {{ t('posts.cancel') }}
                        </Link>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            <span
                                v-if="isSubmitting"
                                class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                            />
                            {{
                                isSubmitting
                                    ? t('posts.creating')
                                    : t('posts.create_post')
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import MediaUpload from '@/Components/MediaUpload.vue'
import DateTimePicker from '@/Components/DateTimePicker.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const page = usePage()
const errors = computed(() => page.props?.errors || {})

const props = defineProps({
    instagramAccounts: {
        type: Array,
        required: true,
    },
    prefill: {
        type: Object,
        default: null,
    },
    post: {
        type: Object,
        default: null,
    },
})

const isEditMode = computed(() => !!props.post)

const form = ref({
    type: 'feed',
    instagram_account_id: '',
    title: '',
    caption: '',
    scheduled_at: '',
    media: [],
})

// Initialize form with props data
const initializeForm = () => {
    form.value = {
        type: props.post?.type || props.prefill?.type || 'feed',
        instagram_account_id:
            props.post?.instagram_account_id ||
            props.prefill?.instagram_account_id ||
            '',
        title: props.post?.title || props.prefill?.title || '',
        caption: props.post?.caption || props.prefill?.caption || '',
        scheduled_at: props.post?.scheduled_at || '',
        media: props.post?.media
            ? props.post.media.map((media) => ({
                  id: media.id,
                  name: media.filename || 'Existing file',
                  type: media.type,
                  size: media.file_size || 0,
                  preview: media.storage_path
                      ? media.storage_path.startsWith('private/')
                          ? `/media/${media.storage_path}`
                          : `/storage/${media.storage_path}`
                      : null,
                  isExisting: true, // Flag to identify existing media
              }))
            : props.prefill?.media
              ? props.prefill.media.map((media) => ({
                    id: media.id,
                    name: media.filename || 'Existing file',
                    type: media.type,
                    size: media.file_size || 0,
                    preview: media.storage_path
                        ? media.storage_path.startsWith('private/')
                            ? `/media/${media.storage_path}`
                            : `/storage/${media.storage_path}`
                        : null,
                    isExisting: true, // Flag to identify existing media
                    isDuplicated: true, // Flag to identify duplicated media
                }))
              : [],
    }
}

// Initialize form on component mount
initializeForm()

const publishMode = ref(props.post?.scheduled_at ? 'schedule' : 'now')
const isSubmitting = ref(false)

const postTypes = [
    {
        value: 'feed',
        label: t('posts.type.feed'),
        description: t('posts.type.feed_description'),
        icon: 'svg',
    },
    {
        value: 'reel',
        label: t('posts.type.reel'),
        description: t('posts.type.reel_description'),
        icon: 'svg',
    },
    {
        value: 'story',
        label: t('posts.type.story'),
        description: t('posts.type.story_description'),
        icon: 'svg',
    },
    {
        value: 'carousel',
        label: t('posts.type.carousel'),
        description: t('posts.type.carousel_description'),
        icon: 'svg',
    },
]

const maxMediaFiles = computed(() => {
    const limits = {
        feed: 1,
        reel: 1,
        story: 1,
        carousel: 10,
    }
    return limits[form.value.type] || 1
})

const allowedMediaTypes = computed(() => {
    const types = {
        feed: ['image', 'video'],
        reel: ['video'],
        story: ['image', 'video'],
        carousel: ['image', 'video'],
    }
    return types[form.value.type] || ['image', 'video']
})

const minDateTime = computed(() => {
    const now = new Date()
    now.setMinutes(now.getMinutes() + 1)
    return now
})

const userTimezone = computed(() => {
    return Intl.DateTimeFormat().resolvedOptions().timeZone
})

const selectPostType = (type) => {
    form.value.type = type
    // Clear media when changing type
    form.value.media = []
}

const updateMedia = (media) => {
    form.value.media = media
}

const submitForm = async () => {
    isSubmitting.value = true

    try {
        const formData = new FormData()

        // Add basic fields
        formData.append('type', form.value.type)
        formData.append('instagram_account_id', form.value.instagram_account_id)
        formData.append('title', form.value.title || '')
        formData.append('caption', form.value.caption || '')

        // Add method override for PUT requests
        if (props.post) {
            formData.append('_method', 'PUT')
        }

        // Add scheduling
        if (publishMode.value === 'schedule' && form.value.scheduled_at) {
            const scheduledDate =
                form.value.scheduled_at instanceof Date
                    ? form.value.scheduled_at
                    : new Date(form.value.scheduled_at)
            formData.append('scheduled_at', scheduledDate.toISOString())
        }

        // Add media files (only new files, not existing ones)
        let mediaIndex = 0
        const mediaToDelete = []
        const mediaToCopy = []

        form.value.media.forEach((media, index) => {
            if (media.isExisting) {
                if (media.markedForDeletion) {
                    mediaToDelete.push(media.id)
                } else if (media.isDuplicated) {
                    // For duplicated media, we need to copy the file
                    mediaToCopy.push({
                        id: media.id,
                        type: media.type,
                        order: mediaIndex,
                    })
                    mediaIndex++
                }
            } else if (media.file) {
                formData.append(`media[${mediaIndex}][type]`, media.type)
                formData.append(`media[${mediaIndex}][file]`, media.file)
                formData.append(`media[${mediaIndex}][order]`, mediaIndex)
                mediaIndex++
            }
        })

        // Add media to copy (for duplicated posts)
        if (mediaToCopy.length > 0) {
            formData.append('copy_media', JSON.stringify(mediaToCopy))
        }

        // Add media to delete
        if (mediaToDelete.length > 0) {
            formData.append('delete_media', JSON.stringify(mediaToDelete))
        }

        if (props.post) {
            // Update existing post
            router.post(route('posts.update', props.post.id), formData, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    // Success handled by server-side redirect
                },
                onError: () => {
                    // Errors are shown inline via page.props.errors
                },
            })
        } else {
            // Create new post
            router.post(route('posts.store'), formData, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    // Success handled by server-side redirect
                },
                onError: () => {
                    // Errors are shown inline via page.props.errors
                },
            })
        }
    } catch (error) {
        console.error('Error creating post:', error)
    } finally {
        isSubmitting.value = false
    }
}

// Watch for type changes to update media validation
watch(
    () => form.value.type,
    (newType) => {
        // Clear media if it exceeds the new type's limit
        if (form.value.media.length > maxMediaFiles.value) {
            form.value.media = form.value.media.slice(0, maxMediaFiles.value)
        }
    }
)
</script>
