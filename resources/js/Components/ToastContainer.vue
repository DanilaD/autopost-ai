<script setup>
import { useToast } from '@/composables/useToast'
import Toast from '@/Components/Toast.vue'

const { toasts, removeToast } = useToast()
</script>

<template>
    <!-- Toast Container -->
    <div class="fixed top-4 right-4 z-[9999] p-4 pointer-events-none">
        <div class="space-y-2 pointer-events-auto">
            <Toast
                v-for="toast in toasts"
                :key="toast.id"
                :message="toast.message"
                :type="toast.type"
                :duration="toast.duration"
                @close="removeToast(toast.id)"
            />
        </div>
    </div>
</template>
