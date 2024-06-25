<script setup lang="ts">
import { useFormStore, type Question } from '@/stores/form';
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import RadioButton from 'primevue/radiobutton';
import { onMounted, ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus'
import DynamicForm from './DynamicForm.vue'


const formStore = useFormStore();
const { form, addQuestion, removeQuestion, addOption, removeOption, getFileTypeOptions, getTextTypeOptions, replaceQuestions } = formStore;

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
const props = defineProps<{ errors?: string; showPreview?: boolean }>()

onMounted(() => {
    if (model.value.length < 1) {
        // The default question is a short text
        if (form.questions.length) {
            model.value = form.questions
        } else {
            addQuestion('text')
        }
    } else {
        // use model value of specified
        replaceQuestions(model.value)
    }
})

watch(form.questions, (value) => {
    model.value = value
})

</script>
<template>
    <div class="">
        <DynamicForm title="" description="" v-if="showPreview" :questions="form.questions" />

        <!-- <div class="mb-4">
            <label for="formTitle" class="block text-sm font-medium text-gray-700">Form Title:</label>
            <InputText class="w-full" id="formTitle" v-model="form.title" placeholder="Enter form title" />
        </div>
        <div class="mb-4">
            <label for="formDescription">Form Description:</label>
            <Textarea class="w-full" id="formDescription" v-model="form.description"
                placeholder="Enter form description"></Textarea>
        </div> -->
        <template v-else>
            <VueDraggable ref="el" v-model="form.questions">
                <TransitionGroup name="slide-fade">
                    <div v-for="(question, index) in form.questions" :key="question.question_id"
                        class="mb-4 px-4 pb-2 pt-8 md:px-4 md:pt-6 md:pb-2 border bg-surface-0 rounded-lg relative">
                        <div class="mb-4">
                            <label class="block text-sm w-full font-medium text-gray-700 mb-2">Question {{ index + 1
                                }}:</label>
                            <div class="flex flex-col md:flex-row flex-wrap md:flex-nowrap gap-2">
                                <InputText class="md:w-8/12" v-model="question.question" placeholder="Enter question" />
                                <Dropdown class='md:w-4/12' v-model="question.type" :options="options"
                                    option-value="value" option-label="label" placeholder="Choose question type">
                                </Dropdown>
                            </div>
                        </div>

                        <div v-if="question.type === 'linear-scale'" class="mb-4">
                            <div class="grid gap-2">
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-1">
                                        <label class="block text-sm font-medium text-gray-700">Min Value:</label>
                                        <InputNumber type="number" v-model.number="question.linear_scale_options.min"
                                            class="w-full" />
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Label:</label>
                                        <InputText v-model="question.linear_scale_options.minLabel" class="w-full" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-1">
                                        <label class="block text-sm font-medium text-gray-700">Max Value:</label>
                                        <InputNumber type="number" v-model.number="question.linear_scale_options.max"
                                            class="w-full" />
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Label:</label>
                                        <InputText v-model="question.linear_scale_options.maxLabel" class="w-full" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Step:</label>
                                    <InputNumber type="number" v-model.number="question.linear_scale_options.step" />
                                </div>
                            </div>
                        </div>

                        <div v-if="question.type === 'radio' || question.type === 'checkbox' || question.type === 'dropdown'"
                            class="mb-4">
                            <div class="font-bold mb-2">Options</div>
                            <div v-for="(option, optIndex) in question.options" :key="optIndex"
                                class="mb-2 flex gap-2 items-center">
                                <!-- <InputText class="w-full" v-model="option.label" :placeholder="`Option ${optIndex + 1}`" /> -->
                                <InputText class="w-full" v-model="option.value"
                                    :placeholder="`Option ${optIndex + 1}`" />
                                <Button class="w-ful" plain severity="danger"
                                    @click="removeOption(index, optIndex)">x</Button>
                            </div>
                            <Button outlined @click="addOption(index)">Add Option</Button>
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
                            <div class="form-group">
                                <label class="block">Allow Only:</label>
                                <div class="flex gap-4">
                                    <div v-for="option of getTextTypeOptions()" :key="option.value"
                                        class="flex gap-2 items-center">
                                        <RadioButton v-model="question.regex" :inputId="option.value" name="option"
                                            :value="option.label" />
                                        <label class="!my-0" :for="option.value">{{ option.label }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="question.type === 'textarea'" class="mb-4">
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
                                            <InputNumber v-model.number="question.text_options.min"
                                                name="word_count_min" type="number" :min="0"
                                                :style="{ width: '100%' }" />
                                        </div>
                                        <div class="form-group w-ful">
                                            <label for="word_count_max">Max Word Count</label>
                                            <InputNumber v-model.number="question.text_options.max"
                                                name="word_count_max" type="number" :min="0"
                                                :style="{ width: '100%' }" />
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <!-- Required checkbox -->
                        <!-- <div class="form-group mb-4">
                    <label class="flex gap-2">
                        <Checkbox v-model="question.required" name="required" class="" binary />
                        <span>Required</span>
                    </label>
                </div> -->
                        <div class="absolute z-10 top-4 right-4">
                            <Button v-if="form.questions.length > 1" size="small" severity="danger" outlined
                                @click="removeQuestion(index)" label="x" class="text-xs"></Button>
                        </div>
                    </div>
                </TransitionGroup>
            </VueDraggable>

            <div v-if="errors" class="text-red-500 mb-4"> {{ errors }} </div>

            <div class="flex flex-wrap gap-2 mb-4">
                <Button outlined @click="() => addQuestion('text')" label="Add Short Text"></Button>
                <Button outlined @click="() => addQuestion('textarea')" label="Add Long Text"></Button>
                <Button outlined @click="() => addQuestion('dropdown')" label="Add Dropdown"></Button>
                <Button outlined @click="() => addQuestion('checkbox')" label="Add Checkbox"></Button>
                <Button outlined @click="() => addQuestion('radio')" label="Add Radio"></Button>
                <Button outlined @click="() => addQuestion('linear-scale')" label="Add Linear Scale"></Button>
                <Button outlined @click="() => addQuestion('file')" label="Add File Upload"></Button>
            </div>
        </template>
    </div>
</template>

<style>
/* Add any additional styles here */
</style>