<script setup>
import { ref } from 'vue'

/**
 * Tooltip Component
 *
 * Displays a tooltip on hover with smooth animations
 *
 * @example
 * <Tooltip text="This is a helpful tooltip">
 *   <button>Hover me</button>
 * </Tooltip>
 */

const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    position: {
        type: String,
        default: 'top',
        validator: (value) =>
            ['top', 'bottom', 'left', 'right'].includes(value),
    },
})

const showTooltip = ref(false)
</script>

<template>
    <div class="relative inline-block">
        <!-- Trigger Element -->
        <div
            @mouseenter="showTooltip = true"
            @mouseleave="showTooltip = false"
            @focus="showTooltip = true"
            @blur="showTooltip = false"
        >
            <slot />
        </div>

        <!-- Tooltip -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="showTooltip"
                :class="[
                    'absolute z-50 px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-lg dark:bg-gray-700',
                    'whitespace-nowrap pointer-events-none',
                    {
                        'bottom-full left-1/2 -translate-x-1/2 mb-2':
                            position === 'top',
                        'top-full left-1/2 -translate-x-1/2 mt-2':
                            position === 'bottom',
                        'right-full top-1/2 -translate-y-1/2 mr-2':
                            position === 'left',
                        'left-full top-1/2 -translate-y-1/2 ml-2':
                            position === 'right',
                    },
                ]"
            >
                {{ text }}

                <!-- Arrow -->
                <div
                    :class="[
                        'absolute w-2 h-2 bg-gray-900 dark:bg-gray-700 rotate-45',
                        {
                            'bottom-0 left-1/2 -translate-x-1/2 translate-y-1':
                                position === 'top',
                            'top-0 left-1/2 -translate-x-1/2 -translate-y-1':
                                position === 'bottom',
                            'right-0 top-1/2 -translate-y-1/2 translate-x-1':
                                position === 'left',
                            'left-0 top-1/2 -translate-y-1/2 -translate-x-1':
                                position === 'right',
                        },
                    ]"
                />
            </div>
        </Transition>
    </div>
</template>
