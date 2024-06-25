<script setup lang="ts">
import { useForm } from 'vee-validate';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import SelectButton from 'primevue/selectbutton';
// import Calendar from 'primevue/calendar';
import QuestionBuilder from './QuestionBuilder.vue';
import { string, object, array } from 'yup';
import Divider from 'primevue/divider';
import { useHomeworkStore, type Homework } from '../../stores/homework';
import { useCourseStore } from '../../stores/course';
import { onMounted, watch, ref } from 'vue';

const props = defineProps<{
    initialValues?: Homework
    // courseId: string
}>();

const { courseId } = useCourseStore();


const { createHomework, updateHomework, loading, homeworkId, getOneHomework, currentHomework } = useHomeworkStore()

const { handleSubmit, errors, defineField, setValues } = useForm({
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
        questions: [],
        ...props.initialValues,
    },
    validationSchema: object({
        title: string().required(),
        description: string().optional(),
        questions: array().min(1, "You must add At least one question")
            .of(
                object().shape({
                    question: string().required("You have to enter a question"),
                    // checked: boolean(),
                })
            )
            .required("You must add At least one question"),
    })
})

const [title, titleProps] = defineField('title');
const [description, descriptionProps] = defineField('description')
const [allow_peer_review, allow_peer_reviewProps] = defineField('allow_peer_review')
const [peer_review_template, peer_review_templateProps] = defineField('peer_review_template')
const [peer_review_who, peer_review_whoProps] = defineField('peer_review_who')
const [questions, questionsProps] = defineField('questions')

const submitForm = handleSubmit(async (values) => {
    // values.date_end = new Date(values.date_end).toISOString().split('T')[0] as unknown as Date
    // TODO: file upload and set the filename
    // values.file_name = ""
    if (props.initialValues?.homework_id) {
        updateHomework({ ...values, subject_id: courseId })
    } else {
        createHomework({ ...values, subject_id: courseId })
    }
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
const key = ref(0);

watch(currentHomework, (value) => {
    if (value) {
        setValues({ ...value, questions: value.questions })
        key.value++
    }
})

onMounted(() => {
    if (homeworkId) {
        getOneHomework(homeworkId)
    }
});
const onUpload = () => {
    // toast.add({ severity: 'info', summary: 'Success', detail: 'File Uploaded', life: 3000 });
};
const showPreview = ref<boolean>(false)
</script>
<template>
    <form id="myFor" class="flex flex-col gap-8" name="Add homework" @submit="submitForm">
        <div class="flex flex-col gap-2">

            <div class="form-group">
                <label for="title">Title</label>
                <InputText v-model="title" v-bind="titleProps" name="title" class="w-full" />
                <div class="p-error text-red-500">{{ errors.title }}</div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <Textarea v-model="description" v-bind="descriptionProps" name="description" rows="5"
                    class="w-full"></Textarea>
                <div class="p-error text-red-500">{{ errors.description }}</div>
            </div>

            <div class="form-group">
                <label class="col-form-label" for="file-3">Upload homework file (optional)</label>
                <div class="input-group mb-2">
                    <FileUpload outlined mode="basic" name="file_name" id="file-3" class=""
                        accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" :maxFileSize="1000000"
                        @upload="onUpload" />
                    <div class="p-error text-red-500">{{ errors.file_name }}</div>
                </div>
                <span class="warning">Max file size up to 10MB</span>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <div class="mb-4">
                <div class="flex gap-4 justify-between items-center ">
                    <h4 class="text-lg font-semibold text-black">Homework Questions</h4>
                    <div>
                        <Button outlined @click="showPreview = !showPreview">{{ showPreview ? "Edit" : "Preview" }}</Button>
                    </div>
                </div>
                <p>How would you like your students to respond to this homework. For example: if you want them to submit
                    a url you can use short text, or if you want the student to respond with
                    a file, you can use the file upload instead</p>
                <Divider class="mb-4" />
            </div>

            <div>
                <QuestionBuilder :showPreview="showPreview" :key="key" :errors="errors.questions" v-model="questions"
                    v-bind="questionsProps">
                </QuestionBuilder>
            </div>

        </div>
        <div class="flex flex-col gap-2">

            <div class="mb-4">
                <h4 class="text-lg font-semibold text-black">Homework Grading</h4>
                <p>How would you like this assessment to be graded?</p>
                <Divider class="mb-4" />
            </div>

            <div class="flex flex-col gap-2">
                <div class="form-group">
                    <label>How would you like this homework to be of assessed?</label>
                    <SelectButton v-model="allow_peer_review" v-bind="allow_peer_reviewProps" name="allow_peer_review"
                        :options="reviewModeOptions" aria-labelledby="basic" option-label="label" option-value="value"
                        size="large" class="" />
                    <div class="p-error text-red-500">{{ errors.allow_peer_review }}</div>

                </div>
                <Transition name="slide-fade">
                    <div v-if="allow_peer_review" class="flex flex-col gap-2">
                        <div class="form-group w-full">
                            <label for="peer_review_who">Who would be reviewing?</label>
                            <SelectButton v-model="peer_review_who" v-bind="peer_review_whoProps" name="peer_review_who"
                                :options="peerReviewWhoOptions" aria-labelledby="basic" option-label="label"
                                option-value="value" size="large" />
                            <div class="p-error text-red-500">{{ errors.peer_review_who }}</div>
                        </div>
                        <div class="form-group w-full">
                            <label for="peer_review_templat">Choose a rubric</label>
                            <Dropdown id="peer_review_templat" :options="peerReviewTemplateOptions"
                                v-model="peer_review_template" v-bind="peer_review_templateProps" class="w-full"
                                option-label="label" option-value="value" />
                            <div class="p-error text-red-500">{{ errors.peer_review_template }}</div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
        <div class="sticky w-full bottom-0 inset-x-0 bg-surface-0">
            <div class="text-center flex items-center max-w-2xl mx-auto justify-center py-4">
                <Button :loading="loading.create || loading.update" class="w-full" type="submit" name="submit" :label="`${homeworkId ? 'Update'
        : 'Add'} Homework`"></Button>
            </div>
        </div>
    </form>
</template>

<style></style>