<script setup lang="ts">
import type { Question } from '../../stores/form';
import { reactive, computed, watch, onMounted } from 'vue';
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import RadioButton from 'primevue/radiobutton'
import Checkbox from 'primevue/checkbox'
import { useToast } from 'primevue/usetoast'
import { } from 'yup'

const props = defineProps<{
    title?: string;
    description?: string;
    initialResponse?: Record<string, any>;
    questions: Question[]
    submit?: (values: Record<string, any>) => void
    submitLabel?: string;
    demo?: boolean
}>();

const toast = useToast()

const responses = reactive<Record<string, any>>(props.initialResponse || {})
const errors = reactive<Record<string, any>>({})

const textAreaQuestions = computed(() => props.questions.filter(q => q.type == 'textarea'))
const validateTextAreas = () => {
    let hasErrors = false;
    textAreaQuestions.value.forEach((q) => {
        if (!isValidWordCount(q.question_id)) {
            hasErrors = true;
        }
    });
    return !hasErrors;
}
function emailIsValid(email: string) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

function urlIsValid(url: string) {
    try {
        return !!new URL(url).href;
    } catch (error) {
        return false
    }
}

const textQuestions = computed(() => props.questions.filter(q => q.type == 'text'))
function isValidText(index: string) {
    const question = props.questions.find(q => q.question_id == index)
    if (!question) return true;
    if (!question.text_options.regex) return true;
    const unescapedRegex = question.text_options.regex.replace(/\\/g, '');
    let isValid = false;
    if (unescapedRegex == 'url') {
        isValid = urlIsValid(responses[question.question_id])
        errors[index] = !isValid ? `${question.question} is not a valid URL` : '';
    } else if (unescapedRegex == 'email') {
        isValid = emailIsValid(responses[question.question_id])
        errors[index] = !isValid ? `${question.question} is not a valid email` : '';
    } else {
        isValid = new RegExp(unescapedRegex).test(responses[question.question_id])
        errors[index] = !isValid ? `${question.question} is invalid` : '';
    }
    return isValid;
}
const validateTexts = () => {
    let hasErrors = false;
    textQuestions.value.forEach((q) => {
        if (!isValidText(q.question_id)) {
            hasErrors = true;
        }
    });
    return !hasErrors;
}

watch(() => Object.values(responses), () => {
    validateTextAreas()
    validateTexts()
})

const handleSubmit = (event: Event) => {
    if (props.demo) { return; }
    if (validateTextAreas() && validateTexts()) {
        console.log("Dynamic Form", event)
        props.submit && props.submit(responses)
    } else {
        toast.add({ severity: "error", summary: "You have invalid fields", life: 5000 })
    }
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
    if (!question.text_options.add_word_count) return true;

    const minWordCount = +(question.text_options.min || 0);
    const maxWordCount = +(question.text_options.max || 0);

    if (wordCount[index] < minWordCount) {
        errors[index] = `${question.question} should be a minimum of ${question.text_options?.min} and a maximum of ${question.text_options.max}`;
        return false;
    } else if (wordCount[index] > maxWordCount) {
        errors[index] = `${question.question} should be a minimum of ${question.text_options?.min} and a maximum of ${question.text_options.max}`;
        // Optional: Disable further typing or truncate text
        responses[index] = truncateToWords(responses[index], maxWordCount); // Truncate to max wordCount.value
        return true;
    } else {
        errors[index] = '';
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
                    <InputText required class="w-full" type="text" :id="question.question_id"
                        v-model="responses[question.question_id]" @input="isValidText(question.question_id)" />
                    <div class="text-red-500">{{ errors[question.question_id] }}</div>
                </div>
                <div class="form-group" v-if="question.type === 'textarea'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <Textarea required @blur="updateWordCount(question.question_id)"
                        @keyup="updateWordCount(question.question_id)" @keypress="updateWordCount(question.question_id)"
                        @load="updateWordCount(question.question_id)" :rows="10" class="w-full"
                        :id="question.question_id" v-model="responses[question.question_id]"></Textarea>
                    <div class="flex gap-2 justify-between items-center" v-if="question.text_options.add_word_count">
                        <div :class="isValidWordCount(question.question_id) ? '' : 'text-red-500'">
                            Word Count: {{ wordCount[question.question_id] ?? 0 }}
                        </div>
                        <div class="">Min: {{ question.text_options.min }} | Max: {{ question.text_options.max }}</div>
                    </div>
                    <div class="text-red-500">{{ errors[question.question_id] }}</div>
                </div>
                <div class="form-group" v-if="question.type === 'linear-scale'">
                    <label>{{ question.question }}</label>
                    <div class="flex flex-col md:flex-row gap-2 md:gap-4 md:items-center">
                        <span>{{ question.linear_scale_options.minLabel }}</span>
                        <div class="flex flex-col md:flex-row gap-2 md:items-center">
                            <span class="flex flex-row md:flex-col gap-2 text-center"
                                v-for="n in range(question.linear_scale_options.min, question.linear_scale_options.max)"
                                :key="n">
                                <input required type="radio" :id="`${question.question_id}-${n}`"
                                    :name="question.question_id" :value="n" v-model="responses[question.question_id]" />
                                <label :for="`${question.question_id}-${n}`">{{ n }}</label>
                            </span>
                        </div>
                        <span>{{ question.linear_scale_options.maxLabel }}</span>
                    </div>
                    <div class="text-red-500">{{ errors[question.question_id] }}</div>
                </div>
                <div class="form-group" v-if="question.type === 'checkbox'">
                    <label>{{ question.question }}</label>
                    <div class="flex gap-2 items-center" v-for="option in question.options" :key="option.value">
                        <Checkbox required type="checkbox" :id="`${question.question_id}-${option.value}`"
                            :value="option.value" v-model="responses[question.question_id]" />
                        <label :for="`${question.question_id}-${option.value}`">{{ option.label }}</label>
                    </div>
                    <div class="text-red-500">{{ errors[question.question_id] }}</div>
                </div>
                <div class="form-group" v-if="question.type === 'radio'">
                    <label>{{ question.question }}</label>
                    <div class="flex gap-2 items-center" v-for="option in question.options" :key="option.value">
                        <RadioButton required type="radio" :id="`${question.question_id}-${option.value}`"
                            :value="option.value" v-model="responses[question.question_id]" />
                        <label :for="`${question.question_id}-${option.value}`">{{ option.label }}</label>
                    </div>
                    <div class="text-red-500">{{ errors[question.question_id] }}</div>
                </div>
                <div class="form-group" v-if="question.type === 'dropdown'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <Dropdown required class="w-full" :id="question.question_id"
                        v-model="responses[question.question_id]" :options="question.options" option-value="value"
                        option-label="label">
                    </Dropdown>
                    <div class="text-red-500">{{ errors[question.question_id] }}</div>
                </div>
                <div class="form-group" v-if="question.type === 'file'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <input required type="file" :id="question.question_id"
                        @change="handleFileUpload(question.question_id, $event)" />
                    <div class="text-red-500">{{ errors[question.question_id] }}</div>
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