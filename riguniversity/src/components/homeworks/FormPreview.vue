<script setup lang="ts">
import type { Question } from '../../stores/form';

const props = defineProps<{
    questions: Question[]
}>();
</script>

<template>
    <div class="form-preview">
        <h2>Form Preview</h2>
        <div v-for="(question, index) in questions" :key="question.question_id" class="preview-question">
            <label class="block text-sm font-medium text-gray-700 mb-2">Question {{ index + 1 }}: {{ question.question
                }}</label>

            <div v-if="question.type === 'text'">
                <input type="text" disabled placeholder="Short text input" class="w-full" />
            </div>

            <div v-if="question.type === 'textarea'">
                <textarea disabled placeholder="Long text input" class="w-full"></textarea>
            </div>

            <div v-if="question.type === 'dropdown'">
                <select disabled class="w-full">
                    <option v-for="option in question.options" :key="option.value" :value="option.value">{{ option.label
                        }}</option>
                </select>
            </div>

            <div v-if="question.type === 'radio'">
                <div v-for="option in question.options" :key="option.value">
                    <input type="radio" :value="option.value" disabled /> {{ option.label }}
                </div>
            </div>

            <div v-if="question.type === 'checkbox'">
                <div v-for="option in question.options" :key="option.value">
                    <input type="checkbox" :value="option.value" disabled /> {{ option.label }}
                </div>
            </div>

            <div v-if="question.type === 'linear-scale'">
                <label>Rating: </label>
                <input type="range" :min="question.linear_scale_options?.min" :max="question.linear_scale_options?.max"
                    :step="question.linear_scale_options?.step" disabled />
            </div>

            <div v-if="question.type === 'file'">
                <label>File Upload:</label>
                <input type="file" disabled />
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-preview {
    border: 1px solid #ccc;
    padding: 16px;
    border-radius: 8px;
}

.preview-question {
    margin-bottom: 16px;
}
</style>