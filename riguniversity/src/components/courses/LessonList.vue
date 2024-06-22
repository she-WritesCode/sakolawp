<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useLessonStore } from '../../stores/lesson';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useCourseStore } from '../../stores/course';
import AddLesson from './AddLesson.vue';
import { useForm } from 'vee-validate';
import { string, object } from 'yup';

const { lessons, filter, lessonId, getOneLesson } = useLessonStore();
const { courseId } = useCourseStore();

onMounted(() => {
    if (lessonId) {
        getOneLesson(lessonId)
    } else {
        filter.meta_query = [{ "key": "sakolawp_subject_id", "value": courseId!, compare: '=' }]
    }
});

const getEditLessonUrl = (lessonId: number) => {

    const url = new URL(window.location.href)
    url.pathname = '/wp-admin/post.php'
    url.search = `?post=${lessonId}&action=edit`
    return url.toString()
}


const { createLesson, loading, } = useLessonStore()

const { handleSubmit, errors, defineField, resetForm } = useForm({
    initialValues: {
        title: '',
        content: '',
        meta: {
            sakolawp_subject_id: courseId!,
        }
    },
    validationSchema: object({
        title: string().required(),
    })
})

const [title, titleProps] = defineField('title');

const submitForm = handleSubmit(async (values) => {

    createLesson({
        ...values, meta: {
            sakolawp_subject_id: `${courseId!}`,
        }
    }).then(() => {
        closeAddForm()
        resetForm()
    })

    console.log('Form submitted:', values)
})

const showAddForm = ref(false)

const openAddForm = () => {
    showAddForm.value = true
}
const closeAddForm = () => {
    showAddForm.value = false
}

</script>
<template>
    <DataTable :value="lessons" tableStyle="min-width: 10rem" paginator :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50]">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <InputText v-model="filter.search" placeholder="Search Lessons" class="font-normal" />
                </div>
                <div class="w-full" v-if="showAddForm">
                    <form id="myForm" name="Add lesson" @submit="submitForm">
                        <div class="flex flex-row items-center gap-2">
                            <div class="form-group w-full">
                                <InputText placeholder="Lesson Title" v-model="title" v-bind="titleProps" name="title"
                                    class="w-full" />
                                <div class="p-error text-red-500">{{ errors.title }}</div>
                            </div>
                            <Button :loading="loading.create || loading.update" class="min-w-64 " type="submit"
                                name="submit" label="Add Lesson"></Button>

                        </div>
                    </form>
                </div>
                <div v-else class="">
                    <Button @click="openAddForm" size="small" label="Add Lesson"></Button>
                </div>
            </div>
        </template>
        <Column field="title" header="Title"></Column>
        <Column field="author" header="Author"></Column>
        <Column header="">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <a :href="getEditLessonUrl(slotProps.data.ID)"><Button size="small" label="Edit"></Button></a>
                    <Button size="small" text severity="danger" label="Delete"></Button>
                </div>
            </template>
        </Column>
    </DataTable>

</template>

<style scoped></style>