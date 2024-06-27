<script setup lang="ts">
import type { Question } from '../../stores/form';
import { reactive } from 'vue';
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import RadioButton from 'primevue/radiobutton'
import Checkbox from 'primevue/checkbox'

const props = defineProps<{
    title?: string;
    description?: string;
    initialResponse?: Record<string, any>;
    questions: Question[]
    submit?: (values: Record<string, any>) => void
    submitLabel?: string;
    demo?: boolean
}>();

const responses = reactive<Record<string, any>>(props.initialResponse || {})

const handleSubmit = (event: Event) => {
    console.log("Dynamic Form", event)
    props.submit && props.submit(responses)
}

const handleFileUpload = (questionId: string, event: Event) => {
    const file = (event.target as any)!.files[0];
    responses[questionId] = file;
}

function truncateToWords(text: string, maxWords: number) {
    const words = text.match(/(\S+\s*|\n+)/g); // Split by words and include new lines
    let wordCount = 0;
    let truncatedText = '';

    if (!words) {
        return text;
    }
    for (let i = 0; i < (words.length || 0); i++) {
        const word = words[i];
        if (word.trim() === '') {
            truncatedText += word; // Add new lines and spaces as is
        } else {
            if (wordCount < maxWords) {
                truncatedText += word;
                wordCount++;
            } else {
                truncatedText += '';
                break;
            }
        }
    }

    return truncatedText.trim();
}

const wordCount = reactive<Record<string, number>>({});

function updateWordCount(index: string) {
    const words = (responses[index] ?? '').trim().split(/\s+/).filter(Boolean).length;

    wordCount[index] = words;
}

function isValidWordCount(index: string) {
    const question = props.questions.find(q => q.question_id == index)
    if (!question) return true;

    const minWordCount = +(question.text_options.min || 0);
    const maxWordCount = +(question.text_options.max || 0);

    if (wordCount.value < minWordCount) {
        return false
    } else if (wordCount.value > maxWordCount) {
        // Optional: Disable further typing or truncate text
        responses[index] = truncateToWords(responses[index], maxWordCount); // Truncate to max wordCount.value
        return false;
    } else {
        return true;
    }
}

const range = (min: number, max: number) => {
    return Array.from({ length: max - min + 1 }, (_, i) => i + min);
}
</script>
<template>
    <div>
        <h1>{{ title ?? "" }}</h1>
        <div class="whitespace-break-spaces mb-4">{{ description ?? "" }}</div>
        <form @submit.prevent="handleSubmit">
            <div class="bg-surface-0 border rounded-md p-4" v-for="(question, ) in questions"
                :key="question.question_id">
                <div class="form-group" v-if="question.type === 'text'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <InputText class="w-full" type="text" :id="question.question_id"
                        v-model="responses[question.question_id]" :pattern="question.text_options.regex"
                        title="invalid input" />
                </div>
                <div class="form-group" v-if="question.type === 'textarea'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <Textarea @blur="updateWordCount(question.question_id)"
                        @keyup="updateWordCount(question.question_id)" @keypress="updateWordCount(question.question_id)"
                        @load="updateWordCount(question.question_id)" :rows="10" class="w-full"
                        :id="question.question_id" v-model="responses[question.question_id]"></Textarea>
                    <div class="flex gap-2 justify-between items-center" v-if="question.text_options.add_word_count">
                        <div :class="isValidWordCount(question.question_id) ? '' : 'text-red-500'">
                            Word Count: {{ wordCount[question.question_id] ?? 0 }}
                        </div>
                        <div class="">Min: {{ question.text_options.min }} | Max: {{ question.text_options.max }}</div>
                    </div>
                </div>
                <div class="form-group" v-if="question.type === 'linear-scale'">
                    <label>{{ question.question }}</label>
                    <div class="flex flex-col md:flex-row gap-2 md:gap-4 md:items-center">
                        <span>{{ question.linear_scale_options.minLabel }}</span>
                        <div class="flex flex-col md:flex-row gap-2 md:items-center">
                            <span class="flex flex-row md:flex-col gap-2 text-center"
                                v-for="n in range(question.linear_scale_options.min, question.linear_scale_options.max)"
                                :key="n">
                                <input type="radio" :id="`${question.question_id}-${n}`" :name="question.question_id"
                                    :value="n" v-model="responses[question.question_id]" />
                                <label :for="`${question.question_id}-${n}`">{{ n }}</label>
                            </span>
                        </div>
                        <span>{{ question.linear_scale_options.maxLabel }}</span>
                    </div>
                </div>
                <div class="form-group" v-if="question.type === 'checkbox'">
                    <label>{{ question.question }}</label>
                    <div class="flex gap-2 items-center" v-for="option in question.options" :key="option.value">
                        <Checkbox type="checkbox" :id="`${question.question_id}-${option.value}`" :value="option.value"
                            v-model="responses[question.question_id]" />
                        <label :for="`${question.question_id}-${option.value}`">{{ option.label }}</label>
                    </div>
                </div>
                <div class="form-group" v-if="question.type === 'radio'">
                    <label>{{ question.question }}</label>
                    <div class="flex gap-2 items-center" v-for="option in question.options" :key="option.value">
                        <RadioButton type="radio" :id="`${question.question_id}-${option.value}`" :value="option.value"
                            v-model="responses[question.question_id]" />
                        <label :for="`${question.question_id}-${option.value}`">{{ option.label }}</label>
                    </div>
                </div>
                <div class="form-group" v-if="question.type === 'dropdown'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <Dropdown class="w-full" :id="question.question_id" v-model="responses[question.question_id]"
                        :options="question.options" option-value="value" option-label="label">
                    </Dropdown>
                </div>
                <div class="form-group" v-if="question.type === 'file'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <input type="file" :id="question.question_id"
                        @change="handleFileUpload(question.question_id, $event)" />
                </div>
            </div>
            <Button v-if="!demo" type="submit">{{ submitLabel || 'Submit' }}</Button>
        </form>
    </div>
</template>

<style scoped>
form {
    display: flex;
    flex-direction: column;
}

form>div {
    margin-bottom: 1rem;
}

.form-group label {
    @apply text-base font-medium;
}
</style>