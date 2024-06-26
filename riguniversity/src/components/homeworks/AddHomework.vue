<script setup lang="ts">
import { useForm } from 'vee-validate';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import SelectButton from 'primevue/selectbutton';
import Calendar from 'primevue/calendar';
import QuestionBuilder from './QuestionBuilder.vue';
import { string, object } from 'yup';
import Stepper, { type StepperChangeEvent } from 'primevue/stepper';
import StepperPanel from 'primevue/stepperpanel';

const { handleSubmit, errors, defineField } = useForm({
    initialValues: {
        title: '',
        description: '',
        file_name: '',
        allow_peer_review: false,
        peer_review_template: '',
        peer_review_who: 'student',
        word_count_min: null,
        word_count_max: null,
        limit_word_count: false,
        date_end: new Date(),
        time_end: "23:59",
        responses: []
    },
    validationSchema: object({
        title: string().required(),
        description: string().optional(),
    })
})

const [title, titleProps] = defineField('title');
const [description, descriptionProps] = defineField('description')
const [allow_peer_review, allow_peer_reviewProps] = defineField('allow_peer_review')
const [peer_review_template, peer_review_templateProps] = defineField('peer_review_template')
const [peer_review_who, peer_review_whoProps] = defineField('peer_review_who')
const [word_count_min, word_count_minProps] = defineField('word_count_min')
const [word_count_max, word_count_maxProps] = defineField('word_count_max')
const [limit_word_count, limit_word_countProps] = defineField('limit_word_count')
const [date_end, date_endProps] = defineField('date_end')
const [time_end, time_endProps] = defineField('time_end')
const [responses, responsesProps] = defineField('responses')

const submitForm = handleSubmit(async (values) => {
    // Submit form logic here
    console.log('Form submitted:', values)
})
const reviewModeOptions = [
    { label: 'Faculty assigns a score', value: false },
    { label: 'Use a rubric', value: true },]
const peerReviewWhoOptions = [
    { label: 'Peer', value: 'student' },
    { label: 'Faculty', value: 'teacher' },]
const peerReviewTemplateOptions = [
    { label: 'Select', value: '' },
    { label: 'prophetic_word', value: 'prophetic_word' },
    { label: 'bible_teaching', value: 'bible_teaching' },
];

const checkStep = (event: StepperChangeEvent) => {
    const step = event.index
    if (step == 0) {
        if (errors.value.title || errors.value.description || errors.value.file_name) {
            throw new Error("No title selected")
        }
    }
    return;
}

const goBack = () => {
    window.history.back();
}
</script>
<template>
    <form id="myFor" class="flex flex-col gap-2" name="Add homework" @submit="submitForm">

        <Stepper @step-change="checkStep" linear orientation="horizontal">
            <StepperPanel header="Details">
                <template #content="{ nextCallback }">
                    <div class="flex flex-col gap-2">

                        <div class="form-group">
                            <label for="title">Title</label>
                            <InputText v-model="title" v-bind="titleProps" name="title" class="w-full" />
                            <div class="p-error text-red-500">{{ errors.title }}</div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <Textarea v-model="description" v-bind="descriptionProps" name="description"
                                class="w-full"></Textarea>
                            <div class="p-error text-red-500">{{ errors.description }}</div>
                        </div>
                        <div class="flex gap-2">
                            <div class="">
                                <div class="form-group">
                                    <label for=""> Due Date</label>
                                    <Calendar v-model="date_end" v-bind="date_endProps" class="w-full"
                                        name="date_end" />
                                    <div class="p-error text-red-500">{{ errors.date_end }}</div>
                                </div>
                            </div>
                            <div class="">
                                <div class="form-group">
                                    <label for=""> Due Time</label>
                                    <Calendar id="calendar-timeonly" v-model="time_end" v-bind="time_endProps" timeOnly
                                        name="time_end" hourFormat="24" />
                                    <div class="p-error text-red-500">{{ errors.time_end }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label" for="file-3">Upload homework file (optional)</label>
                            <div class="input-group mb-2">
                                <FileUpload outlined mode="basic" name="file_name" id="file-3" class=""
                                    accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" />
                                <div class="p-error text-red-500">{{ errors.file_name }}</div>
                            </div>
                            <span class="warning">Max file size up to 10MB</span>
                        </div>
                    </div>
                    <div class="flex justify-between py-4">
                        <Button severity="secondary" label="Cancel" @click="goBack" />
                        <Button label="Next" @click="nextCallback" />
                    </div>
                </template>
            </StepperPanel>
            <StepperPanel header="Assessment">
                <template #content="{ prevCallback, nextCallback }">
                    <div class="flex flex-col gap-2">

                        <div>
                            <h4 class="text-lg font-medium">Homework Assessment</h4>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="form-group">
                                <label>How would you like this homework to be of assessed?</label>
                                <SelectButton v-model="allow_peer_review" v-bind="allow_peer_reviewProps"
                                    name="allow_peer_review" :options="reviewModeOptions" aria-labelledby="basic"
                                    option-label="label" option-value="value" size="large" class="" />
                                <div class="p-error text-red-500">{{ errors.allow_peer_review }}</div>

                            </div>
                            <Transition name="slide-fade">
                                <div v-if="allow_peer_review" class="flex flex-col gap-2">
                                    <div class="form-group w-full">
                                        <label for="peer_review_who">Who would be reviewing?</label>
                                        <SelectButton v-model="peer_review_who" v-bind="peer_review_whoProps"
                                            name="peer_review_who" :options="peerReviewWhoOptions"
                                            aria-labelledby="basic" option-label="label" option-value="value"
                                            size="large" />
                                        <div class="p-error text-red-500">{{ errors.peer_review_who }}</div>
                                    </div>
                                    <div class="form-group w-full">
                                        <label for="peer_review_template">Choose a rubric</label>
                                        <Dropdown name="peer_review_template" id="peer_review_template"
                                            :options="peerReviewTemplateOptions" v-model="peer_review_template"
                                            v-bind="peer_review_templateProps" class="w-full" option-label="label"
                                            option-value="value" />
                                        <div class="p-error text-red-500">{{ errors.peer_review_template }}</div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                    <div class="flex py-4 gap-2 justify-between">
                        <Button label="Back" severity="secondary" @click="prevCallback" />
                        <Button label="Next" @click="nextCallback" />
                    </div>
                </template>
            </StepperPanel>
            <StepperPanel header="Responses">
                <template #content="{ prevCallback }">
                    <div class="flex flex-col gap-2">

                        <div>
                            <h4 class="text-lg font-medium">Homework Responses</h4>
                            <p>How would you like your students to respond to this homework</p>
                        </div>

                        <div>
                            <QuestionBuilder v-model="responses" v-bind="responsesProps"></QuestionBuilder>
                            <div class="p-error text-red-500">{{ errors.responses }}</div>
                        </div>

                    </div>
                    <div class="flex gap-4 py-4 justify-between">
                        <Button label="Back" severity="secondary" @click="prevCallback" />
                        <Button class="w-1/2" type="submit" name="submit" label="Add Homework"></Button>
                    </div>
                </template>
            </StepperPanel>
        </Stepper>

    </form>
</template>

<style></style>