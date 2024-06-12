<script setup lang="ts">
import { useFormStore, type QuestionType, type Question } from '@/stores/form';
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import { onMounted, ref, watch } from 'vue';


const formStore = useFormStore();
const { form, addQuestion, removeQuestion, addOption, removeOption, getFileTypeOptions, replaceQuestions } = formStore;

const options = [
    { label: "Short Text", value: "text" },
    { label: "Long Text", value: "textarea" },
    { label: "Dropdown", value: "dropdown" },
    { label: "Radio", value: "radio" },
    { label: "Checkbox", value: "checkbox" },
    { label: "Linear Scale", value: "linear-scale" },
    { label: "File Upload", value: "file" },
]

const model = defineModel<Question[]>({ default: [] })
const showPreview = ref<boolean>(false)

onMounted(() => {
    console.log("onMounted", model.value)
    // The default question is a short text
    if (model.value.length < 1) {
        if (form.questions.length) {
            model.value = form.questions
        } else { addQuestionHandler('text') }
    } else {
        replaceQuestions(model.value)
    }
})

// watch(model, (value) => {
//     console.log("watch", value)
//     replaceQuestions(value)
// })

const addQuestionHandler = (type: QuestionType) => {
    addQuestion(type)
    model.value = form.questions
}
const removeQuestionHandler = (index: number) => {
    removeQuestion(index)
    model.value = form.questions
}
const addOptionHandler = (questionIndex: number) => {
    addOption(questionIndex)
    model.value = form.questions
}
const removeOptionHandler = (questionIndex: number, optionIndex: number) => {
    removeOption(questionIndex, optionIndex)
    model.value = form.questions
}
</script>
<template>
    <div class="">
        <!-- <Button @click="showPreview = !showPreview">Preview</Button>
        <FormPreview v-if="showPreview" :questions="form.questions" /> -->

        <!-- <div class="mb-4">
            <label for="formTitle" class="block text-sm font-medium text-gray-700">Form Title:</label>
            <InputText class="w-full" id="formTitle" v-model="form.title" placeholder="Enter form title" />
        </div>
        <div class="mb-4">
            <label for="formDescription">Form Description:</label>
            <Textarea class="w-full" id="formDescription" v-model="form.description"
                placeholder="Enter form description"></Textarea>
        </div> -->

        <TransitionGroup name="slide-fade">
            <div v-for="(question, index) in form.questions" :key="question.question_id"
                class="mb-4 px-4 pb-2 pt-8 md:px-4 md:pt-6 md:pb-4 border rounded-lg relative">
                <div class="mb-4">
                    <label class="block text-sm w-full font-medium text-gray-700 mb-2">Question {{ index + 1 }}:</label>
                    <div class="flex flex-col md:flex-row flex-wrap gap-2">
                        <Dropdown class='md:w-4/12' v-model="question.type" :options="options" option-value="value"
                            option-label="label" placeholder="Choose question type">
                        </Dropdown>
                        <InputText class="md:w-8/12" v-model="question.question" placeholder="Enter question" />
                    </div>
                </div>

                <div v-if="question.type === 'linear-scale'" class="mb-4">
                    <div class="grid md:grid-cols-3 gap-4">
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
                    <div v-for="(option, optIndex) in question.options" :key="optIndex"
                        class="mb-2 flex gap-2 items-center">
                        <!-- <InputText class="w-full" v-model="option.label" :placeholder="`Option ${optIndex + 1}`" /> -->
                        <InputText class="w-full" v-model="option.value" :placeholder="`Option ${optIndex + 1}`" />
                        <Button class="w-ful" plain severity="warning"
                            @click="removeOptionHandler(index, optIndex)">x</Button>
                    </div>
                    <Button @click="addOptionHandler(index)">Add Option</Button>
                </div>


                <div v-if="question.type === 'file'" class="mb-4">
                    <div class="form-group">
                        <label class="block">Accept:</label>
                        <Dropdown class='md:w-4/12' v-model="question.accepts" :options="getFileTypeOptions()"
                            option-value="value" option-label="label" placeholder="Choose file type">
                        </Dropdown>
                    </div>
                    <div class="form-group">
                        <label class="flex gap-2">
                            <Checkbox v-model="question.multiple" name="allow_multiple" class="" binary />
                            <span>Allow multiple files</span>
                        </label>
                    </div>
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
                                <Checkbox v-model="question.text_options.add_word_count" name="limit_word_count"
                                    class="" binary />
                                <span>Limit word count</span>
                            </label>
                        </div>
                        <Transition name="slide-fade">
                            <div v-if="question.text_options?.add_word_count" class="flex gap-2">
                                <div class="form-group w-ful">
                                    <label for="word_count_min">Min Word Count</label>
                                    <InputNumber v-model.number="question.text_options.min" name="word_count_min"
                                        type="number" :min="0" :style="{ width: '100%' }" />
                                </div>
                                <div class="form-group w-ful">
                                    <label for="word_count_max">Max Word Count</label>
                                    <InputNumber v-model.number="question.text_options.max" name="word_count_max"
                                        type="number" :min="0" :style="{ width: '100%' }" />
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- Required checkbox -->
                <div class="form-group mb-4">
                    <label class="flex gap-2">
                        <Checkbox v-model="question.required" name="required" class="" binary />
                        <span>Required</span>
                    </label>
                </div>
                <div class="absolute z-10 top-4 right-4">
                    <Button v-if="form.questions.length > 1" size="small" severity="danger" outlined
                        @click="removeQuestionHandler(index)" label="x" class="text-xs"></Button>
                </div>
            </div>
        </TransitionGroup>

        <div class="flex flex-wrap gap-2 mb-4">
            <Button outlined @click="() => addQuestionHandler('text')" label="Add Short Text"></Button>
            <Button outlined @click="() => addQuestionHandler('textarea')" label="Add Long Text"></Button>
            <Button outlined @click="() => addQuestionHandler('dropdown')" label="Add Dropdown"></Button>
            <Button outlined @click="() => addQuestionHandler('checkbox')" label="Add Checkbox"></Button>
            <Button outlined @click="() => addQuestionHandler('radio')" label="Add Radio"></Button>
            <Button outlined @click="() => addQuestionHandler('linear-scale')" label="Add Linear Scale"></Button>
            <Button outlined @click="() => addQuestionHandler('file')" label="Add File Upload"></Button>
        </div>

    </div>
</template>

<style>
/* Add any additional styles here */
</style>