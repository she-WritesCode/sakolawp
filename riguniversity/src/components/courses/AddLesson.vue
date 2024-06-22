<script setup lang="ts">
import { useForm } from 'vee-validate';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { string, object } from 'yup';
import { useLessonStore } from '../../stores/lesson';
import { onMounted } from 'vue';

const props = defineProps<{
    courseId: number
}>()

const { createLesson, loading, lessonId, getOneLesson } = useLessonStore()

const { handleSubmit, errors, defineField } = useForm({
    initialValues: {
        title: '',
        content: '',
        meta: {
            sakolawp_subject_id: props.courseId!,
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
            sakolawp_subject_id: `${props.courseId!}`,
        }
    })

    console.log('Form submitted:', values)
})


onMounted(() => {
    if (lessonId) {
        getOneLesson(lessonId)
    }
});

</script>
<template>
    <form id="myFor" class="flex flex-col gap-4" name="Add lesson" @submit="submitForm">
        <div class="flex flex-col gap-2">

            <div class="form-group">
                <label for="title">Lesson Title</label>
                <InputText v-model="title" v-bind="titleProps" name="title" class="w-full" />
                <div class="p-error text-red-500">{{ errors.title }}</div>
            </div>
        </div>
        <div class="sticky w-full bottom-0 inset-x-0 bg-surface-0">
            <div class="text-center flex items-center max-w-2xl mx-auto justify-center py-4">
                <Button :loading="loading.create || loading.update" class="w-full" type="submit" name="submit" :label="`${lessonId ? 'Update'
        : 'Add'} Lesson`"></Button>
            </div>
        </div>
    </form>
</template>

<style></style>