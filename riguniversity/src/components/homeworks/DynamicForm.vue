<script setup lang="ts">
import type { Question } from '../../stores/form';
import { reactive } from 'vue';
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import RadioButton from 'primevue/radiobutton'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'

const props = defineProps<{
    title: string;
    description: string;
    questions: Question[]
    submit?: (values: Record<string, any>) => void
    submitLabel?: string;
}>();

const responses = reactive<Record<string, any>>({})

const handleSubmit = (values: Record<string, any>) => {
    console.log("Dynamic Form", values)
    props.submit && props.submit(values)
}

const handleFileUpload = (questionId: string, event: Event) => {
    const file = (event.target as any)!.files[0];
    responses[questionId] = file;
}

const range = (min: number, max: number) => {
    return Array.from({ length: max - min + 1 }, (_, i) => i + min);
}
</script>
<template>
    <div>
        <h1>{{ title }}</h1>
        <p>{{ description }}</p>
        <form @submit.prevent="handleSubmit">
            <div class="bg-surface-50 p-4" v-for="question in questions" :key="question.question_id">
                <div class="form-group" v-if="question.type === 'text'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <InputText class="w-full" type="text" :id="question.question_id"
                        v-model="responses[question.question_id]" />
                </div>
                <div class="form-group" v-if="question.type === 'textarea'">
                    <label :for="question.question_id">{{ question.question }}</label>
                    <Textarea rows="5" class="w-full" :id="question.question_id"
                        v-model="responses[question.question_id]" />
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
            <Button type="submit">{{ submitLabel || 'Submit' }}</Button>
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
</style>