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

const { homeworks, goToAddForm, filter, goToViewHomework, homeworkId, showAddForm, closeAddForm, getOneHomework } = useHomeworkStore();
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
    <div v-if="showAddForm">
        <div class="md:px-2 max-w-2xl mx-auto">
            <div class="flex gap-2 mb-8 items-center">
                <div>
                    <Button @click="closeAddForm" label="Back" size="small" outline severity="secondary"></Button>
                </div>
                <div class="md:text-center w-full">
                    <h3 class="px-2 text-xl font-semibold">Add Homework</h3>
                </div>
            </div>
            <AddHomework></AddHomework>
        </div>
    </div>
    <DataTable v-else :value="homeworks" tableStyle="min-width: 10rem" paginator :rows="10"
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

    <!-- <Dialog @close-modal="closeAddForm" v-model:visible="showAddForm" modal class="w-full md:w8/12">
        <template #container="{ closeCallback }">
            <div class="px-2 pt-8">
                <h3 class="px-2 text-xl">Add Homework</h3>
                <AddHomework></AddHomework>
            </div>
        </template>
    </Dialog> -->
</template>

<style scoped></style>