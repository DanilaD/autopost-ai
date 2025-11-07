<template>
    <div class="p-4 bg-yellow-100 rounded-lg border border-yellow-300">
        <h3 class="text-lg font-semibold text-yellow-800">Translation Debug</h3>

        <div class="mt-4 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Current Locale:</span>
                <span class="text-sm">{{ currentLocale }}</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Available Locales:</span>
                <span class="text-sm">{{ availableLocales }}</span>
            </div>

            <div class="mt-4">
                <h4 class="font-semibold text-yellow-800">
                    Translation Tests:
                </h4>
                <div class="mt-2 space-y-1 text-sm">
                    <div>
                        ai.input_title: "{{
                            testTranslation('ai.input_title')
                        }}"
                    </div>
                    <div>
                        ai.advanced_options: "{{
                            testTranslation('ai.advanced_options')
                        }}"
                    </div>
                    <div>
                        ai.temperature: "{{
                            testTranslation('ai.temperature')
                        }}"
                    </div>
                    <div>
                        ai.max_tokens: "{{ testTranslation('ai.max_tokens') }}"
                    </div>
                    <div>
                        ai.no_result: "{{ testTranslation('ai.no_result') }}"
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h4 class="font-semibold text-yellow-800">i18n Status:</h4>
                <div class="mt-2 text-sm">
                    <div>
                        i18n object exists: {{ i18nExists ? 'YES' : 'NO' }}
                    </div>
                    <div>
                        messages exists: {{ messagesExist ? 'YES' : 'NO' }}
                    </div>
                    <div>
                        en messages exist: {{ enMessagesExist ? 'YES' : 'NO' }}
                    </div>
                    <div>
                        ai section exists: {{ aiSectionExists ? 'YES' : 'NO' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale, messages, t } = useI18n()

const currentLocale = computed(() => locale.value || 'Not available')
const availableLocales = computed(() => {
    try {
        return messages.value
            ? Object.keys(messages.value).join(', ')
            : 'Not available'
    } catch (error) {
        return 'Error: ' + error.message
    }
})

const i18nExists = computed(() => !!messages.value)
const messagesExist = computed(() => !!messages.value)
const enMessagesExist = computed(() => !!(messages.value && messages.value.en))
const aiSectionExists = computed(
    () => !!(messages.value && messages.value.en && messages.value.en.ai)
)

const testTranslation = (key) => {
    try {
        return t(key)
    } catch (error) {
        return 'Error: ' + error.message
    }
}
</script>
