<script setup lang="ts">
import { onMounted } from 'vue';
import { useHomeworkStore } from '../../stores/homework';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useSubjectStore } from '../../stores/subject';

const { homeworks, fetchHomeworks, filter, goToViewHomework, homeworkId, currentHomework, getOneHomework, loading } = useHomeworkStore();
const { subjectId } = useSubjectStore();

onMounted(() => {
    if (homeworkId) {
        getOneHomework(homeworkId)
    } else {
        filter.subject_id = subjectId ?? ""
        fetchHomeworks();
    }
});

</script>
<template>

    <DataTable :value="homeworks" tableStyle="min-width: 10rem" paginator :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50]">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <InputText v-model="filter.search" placeholder="Search Homeworks" class="pl-10 font-normal" />
                </div>
                <div class="">
                    <Button size="small" label="Add Homework"></Button>
                </div>
            </div>
        </template>
        <Column field="title" header="Title"></Column>
        <Column header="Release Date">
            <template #body="slotProps">
                <div>{{ slotProps.data.date_end }} {{ slotProps.data.time_end }}</div>
            </template>
        </Column>
        <Column header="Deadline">
            <template #body="slotProps">
                <div>{{ slotProps.data.date_end }} {{ slotProps.data.time_end }}</div>
            </template>
        </Column>
        <Column header="Submissions" class="text-center">
            <template #body="slotProps">
                <Tag :value="slotProps.data.delivery_count" severity="secondary" />
            </template>
        </Column>
        <Column field="teacher_name" header="Faculty"></Column>
        <Column header="">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <Button size="small" outline @click="goToViewHomework(slotProps.data.homework_id)"
                        label="Details"></Button>
                    <Button size="small" text severity="danger" label="Delete"></Button>
                </div>
            </template>
        </Column>
    </DataTable>
</template>

<style scoped></style>