<script setup lang="ts">
import { useFormStore, type QuestionType, type Question } from '@/stores/form';
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'


const formStore = useFormStore();
const { form, addQuestion, removeQuestion, addOption, removeOption } = formStore;

const options = [
    { label: "Short Text", value: "text" },
    { label: "Long Text", value: "textarea" },
    { label: "Dropdown", value: "dropdown" },
    { label: "Radio", value: "radio" },
    { label: "Checkbox", value: "checkbox" },
    { label: "Linear Scale", value: "linear-scale" },
]

const model = defineModel<Question[]>({ default: [] })


const addQuestionHandler = (type: QuestionType) => {
    addQuestion(type)
    model.value = form.questions
}
const removeQuestionHandler = (index: number) => {
    removeQuestion(index)
    model.value = form.questions
}
</script>
<template>
    <div class="max-w-3xl mx-auto">
        <!-- <div class="mb-4">
            <label for="formTitle" class="block text-sm font-medium text-gray-700">Form Title:</label>
            <InputText class="w-full" id="formTitle" v-model="form.title" placeholder="Enter form title" />
        </div>
        <div class="mb-4">
            <label for="formDescription">Form Description:</label>
            <Textarea class="w-full" id="formDescription" v-model="form.description"
                placeholder="Enter form description"></Textarea>
        </div> -->

        <div v-for="(question, index) in form.questions" :key="question.question_id"
            class="mb-4 p-4 border rounded-lg relative">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Question {{ index + 1 }}:</label>
                <InputText v-model="question.question" placeholder="Enter question" />
                <Dropdown v-model="question.type" :options="options" option-value="value" option-label="label">
                </Dropdown>
            </div>

            <div v-if="question.type === 'linear-scale'" class="mb-4">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Min Value:</label>
                        <InputNumber type="number" v-model.number="question.linear_scale_options!.min" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Max Value:</label>
                        <InputNumber type="number" v-model.number="question.linear_scale_options!.max" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Step:</label>
                        <InputNumber type="number" v-model.number="question.linear_scale_options!.step" />
                    </div>
                </div>
            </div>

            <div v-if="question.type === 'radio' || question.type === 'checkbox' || question.type === 'dropdown'"
                class="mb-4">
                <div v-for="(option, optIndex) in question.options" :key="optIndex" class="mb-2 flex items-center">
                    <InputText v-model="option.label" placeholder="Option label" />
                    <InputText v-model="option.value" placeholder="Option value" />
                    <Button outlined severity="danger" @click="removeOption(index, optIndex)">Remove Option</Button>
                </div>
                <Button @click="addOption(index)">Add Option</Button>
            </div>


            <div v-if="question.type === 'text'" class="mb-4">
                <!-- <label class="block text-sm font-medium text-gray-700">Answer:</label>
                <InputText type="text" disabled placeholder="Text input" /> -->
            </div>

            <div v-if="question.type === 'textarea'" class="mb-4">
                <!-- <label class="block text-sm font-medium text-gray-700">Answer:</label>
                <InputText type="text" disabled placeholder="Text input" /> -->
            </div>

            <div v-if="question.type === 'textarea' || question.type === 'text'" class="mb-4">
                <div class="flex flex-col gap-2 text-sm">
                    <div class="form-group w-full">
                        <label class="flex gap-2">
                            <Checkbox v-model="question.text_options.add_word_count" name="limit_word_count" class=""
                                binary />
                            <span>Limit word count</span>
                        </label>
                    </div>
                    <Transition name="slide-fade">
                        <div v-if="question.text_options?.add_word_count">
                            <div class="flex gap-2">
                                <div class="form-group w-full">
                                    <label for="word_count_min">Minimum Word Count</label>
                                    <InputNumber v-model.number="question.text_options.min" name="word_count_min"
                                        type="number" :min="0" class="w-full" />
                                </div>
                                <div class="form-group w-full">
                                    <label for="word_count_max">Maximum Word Count</label>
                                    <InputNumber v-model.number="question.text_options.max" name="word_count_max"
                                        type="number" :min="0" class="w-full" />
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
            <div class="absolute top-4 right-4">
                <Button size="small" severity="danger" outlined @click="removeQuestionHandler(index)" label="x"
                    class="text-xs "></Button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            <Button outlined @click="() => addQuestionHandler('text')" label="Add Short Text"></Button>
            <Button outlined @click="() => addQuestionHandler('textarea')" label="Add Long Text"></Button>
            <Button outlined @click="() => addQuestionHandler('dropdown')" label="Add Dropdown"></Button>
            <Button outlined @click="() => addQuestionHandler('checkbox')" label="Add Checkbox"></Button>
            <Button outlined @click="() => addQuestionHandler('radio')" label="Add Radio"></Button>
            <Button outlined @click="() => addQuestionHandler('linear-scale')" label="Add Linear Scale"></Button>
            <!-- <Button class="w-full" @click="generateForm" label="Generate Form"></Button> -->
        </div>

        <!-- <pre>{{ form }}</pre> Display the form data for debugging -->
    </div>
</template>

<style>
/* Add any additional styles here */
</style>