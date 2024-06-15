<script setup lang="ts">
import { onMounted } from 'vue';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useEmailTemplateStore } from '../stores/email-templates'

const { fetchTemplates, loading, saveTemplates, templates, errors } = useEmailTemplateStore()

onMounted(() => {
    fetchTemplates()
});
</script>

<template>
    <Toast position="bottom-center" />
    <div class="max-w-2xl mx-auto">
        <div v-if="loading.list" class="text-center">Loading...</div>
        <div v-else>
            <div class="flex gap-4 items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Email Templates ({{ templates.length }})</h1>
                <Button @click="saveTemplates" label="Save All Templates"></Button>
            </div>
            <Accordion class="rounded border border-surface-200">
                <AccordionTab v-for="template in templates" :key="template.id">
                    <template #header>
                        <h3 class="font-bold text-primary-700 white-space-nowrap">
                            {{ template.title }}
                            <span v-if="errors[template.id]?.subject || errors[template.id]?.template"
                                class="text-red-500 ml-2">check errors</span>
                        </h3>
                    </template>
                    <div class="py-4">
                        <div class="form-group mb-4">
                            <label>Email Subject</label>
                            <InputText v-model="template.content.subject" class="w-full" />
                            <p class="text-red-500 text-sm">{{ errors[template.id]?.subject }}</p>
                        </div>
                        <div class="form-group mb-4">
                            <label>Email Body</label>
                            <div v-if="template.placeholders.length" class="leading-loose mb-2">
                                The following placeholders are available:
                                <template v-for="(placeholder, index) in template.placeholders" :key="placeholder">
                                    {{ index == 0 ? ' ' : ', ' }}<code>{{ placeholder }}</code>
                                </template>.
                                Indicate placeholder text with curly braces like: <code>{place_holder}</code>
                            </div>
                            <Textarea v-model="template.content.template" class="w-full leading-relaxed" rows="10"
                                placeholder="Enter text"></Textarea>
                            <p class="text-red-500 text-sm">{{ errors[template.id]?.template }}</p>
                            <!-- <Editor v-model="template.content.template" editorStyle="min-height: 280px"
                                placeholder="Enter text" /> -->

                        </div>
                    </div>
                </AccordionTab>
            </Accordion>
        </div>
    </div>
</template>

<style scoped>
code {
    @apply rounded;
}

/* Add any additional styling here */
</style>