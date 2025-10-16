<script setup>
defineProps({
    value: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
