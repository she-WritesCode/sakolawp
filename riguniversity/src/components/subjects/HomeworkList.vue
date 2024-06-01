<script setup lang="ts">
import { onMounted } from 'vue';
import { useHomeworkStore } from '../../stores/homework';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useSubjectStore } from '../../stores/subject';
import AddHomework from '../homeworks/AddHomework.vue';
import Dialog from 'primevue/dialog';

const { homeworks, goToAddForm, filter, goToViewHomework, homeworkId, showAddForm, getOneHomework } = useHomeworkStore();
const { subjectId } = useSubjectStore();

onMounted(() => {
    if (homeworkId) {
        getOneHomework(homeworkId)
    } else {
        filter.subject_id = subjectId ?? ""
        // fetchHomeworks();
    }
});

</script>
<template>

    <DataTable :value="homeworks" tableStyle="min-width: 10rem" paginator :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50]">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <InputText v-model="filter.search" placeholder="Search Homeworks" class="font-normal" />
                </div>
                <div class="">
                    <Button @click="goToAddForm" size="small" label="Add Homework"></Button>
                </div>
            </div>
        </template>
        <Column field="title" header="Title"></Column>
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

    <Dialog v-model:visible="showAddForm" modal header="Add Homework" :style="{ width: '30rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <AddHomework></AddHomework>
    </Dialog>
</template>

<style scoped></style>