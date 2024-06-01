<script setup>
import { reactive, computed } from 'vue';
import { useForm } from 'vee-validate';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import SelectButton from 'primevue/selectbutton';
import Calendar from 'primevue/calendar';


const { handleSubmit, errors, defineField } = useForm()
const formData = reactive({
    title: '',
    description: '',
    allow_peer_review: false,
    peer_review_template: '',
    peer_review_who: 'student',
    word_count_min: null,
    word_count_max: null,
    limit_word_count: false,
    date_end: new Date(),
    time_end: "23:59",
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

const submitForm = handleSubmit(async () => {
    // Submit form logic here
    console.log('Form submitted:', formData)
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
]
</script>
<template>

    <div>
        <form id="myForm" class="flex flex-col gap-2" name="Add homework" @submit="submitForm">
            <div class="form-group">
                <label for="title">Title</label>
                <InputText v-model="title" v-bind="titleProps" name="title" class="w-full" required />
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <Textarea v-model="description" v-bind="descriptionProps" name="description" class="w-full"></Textarea>
            </div>
            <div class="flex gap-2">
                <div class="">
                    <div class="form-group">
                        <label for=""> Due Date</label>
                        <Calendar v-model="date_end" v-bind="date_endProps" class="w-full" name="date_end" />
                    </div>
                </div>
                <div class="">
                    <div class="form-group">
                        <label for=""> Due Time</label>
                        <Calendar id="calendar-timeonly" v-model="time_end" v-bind="time_endProps" timeOnly
                            name="time_end" hourFormat="24" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-form-label" for="file-3">Upload homework file (optional)</label>
                <div class="input-group mb-2">
                    <FileUpload mode="basic" name="file_name" id="file-3" class=""
                        accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" />
                </div>
                <span class="warning">Max file size up to 10MB</span>
            </div>

            <div>
                <h4 class="text-lg font-medium">Homework Responses</h4>
                <p>How would you like your students to respond to this homework</p>
            </div>

            <div class="flex flex-col gap-2">
                <div class="form-group w-full">
                    <label class="flex gap-2">
                        <Checkbox v-model="limit_word_count" v-bind="limit_word_countProps" name="limit_word_count"
                            class="" />
                        <span>Limit word count</span>
                    </label>
                </div>
                <Transition name="slide-fade">
                    <div v-if="limit_word_count">
                        <div class="flex gap-2">
                            <div class="form-group w-full">
                                <label for="word_count_min">Minimum Word Count</label>
                                <InputNumber v-model.number="word_count_min" v-bind="word_count_minProps"
                                    name="word_count_min" type="number" min="0" class="w-full" />
                            </div>
                            <div class="form-group w-full">
                                <label for="word_count_max">Maximum Word Count</label>
                                <InputNumber v-model.number="word_count_max" v-bind="word_count_maxProps"
                                    name="word_count_max" type="number" min="0" class="w-full" />
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>


            <div>
                <h4 class="text-lg font-medium">Homework Assessment</h4>
            </div>

            <div class="flex flex-col gap-2">
                <div class="form-group">
                    <label>How would you like this homework to be of assessed?</label>
                    <SelectButton v-model="allow_peer_review" v-bind="allow_peer_reviewProps" name="allow_peer_review"
                        :options="reviewModeOptions" aria-labelledby="basic" option-label="label"
                        option-value="value" />

                </div>
                <Transition name="slide-fade">
                    <div v-if="allow_peer_review" class="flex gap-2">
                        <div class="form-group w-full">
                            <label for="peer_review_who">Who would be reviewing?</label>
                            <SelectButton v-model="peer_review_who" v-bind="peer_review_whoProps" name="peer_review_who"
                                :options="peerReviewWhoOptions" aria-labelledby="basic" option-label="label"
                                option-value="value" />
                        </div>
                        <div class="form-group w-full">
                            <label for="peer_review_template">Choose a rubric</label>
                            <Dropdown name="peer_review_template" id="peer_review_template"
                                :options="peerReviewTemplateOptions" v-model="peer_review_template"
                                v-bind="peer_review_templateProps" class="w-full" option-label="label"
                                option-value="value" />
                        </div>
                    </div>
                </Transition>
            </div>


            <div class="w-full">
                <Button class="w-full" label="Add Homework"></Button>
            </div>
        </form>
    </div>
</template>

<style></style>