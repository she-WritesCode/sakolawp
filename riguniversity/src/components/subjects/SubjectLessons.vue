<script setup lang="ts">
import { onMounted } from 'vue';
import { useLessonStore } from '../../stores/lesson';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useSubjectStore } from '../../stores/subject';

const { lessons, fetchLessons, filter, goToViewLesson, lessonId, currentLesson, getOneLesson, loading } = useLessonStore();
const { subjectId } = useSubjectStore();

onMounted(() => {
    if (lessonId) {
        getOneLesson(lessonId)
    } else {
        // filter.subject_id = subjectId ?? ""
        // fetchLessons();
    }
});

</script>
<template>
    <DataTable :value="lessons" tableStyle="min-width: 10rem" paginator :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50]">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <InputText v-model="filter.search" placeholder="Search Lessons" class="font-normal" />
                </div>
                <div class="">
                    <Button size="small" label="Add Lesson"></Button>
                </div>
            </div>
        </template>
        <Column field="name" header="Name"></Column>
        <!-- <Column field="name" header="Lessons"></Column> -->
        <Column header="Description">
            <template #body="slotProps">
                <Tag :value="slotProps.data.lesson_count" severity="secondary" />
            </template>
        </Column>
        <Column field="teacher_name" header="Added by"></Column>
        <Column header="">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <Button size="small" label="Edit"></Button>
                    <Button size="small" text severity="danger" label="Delete"></Button>
                </div>
            </template>
        </Column>
    </DataTable>
</template>

<style scoped></style>