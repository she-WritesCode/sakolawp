<script setup lang="ts">
import { onMounted } from "vue";
import { useSubjectStore } from "../stores/subject";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

const { subjects, fetchSubjects, search } = useSubjectStore();

onMounted(() => {
    fetchSubjects();
});
</script>

<template>
    <DataTable :value="subjects" tableStyle="min-width: 10rem" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-xl text-surface-900 dark:text-surface-0 font-bold">Subjects</span>
                <div>
                    <i class="pi pi-search absolute top-2/4 -mt-2 left-3 text-surface-400 dark:text-surface-600"></i>
                    <InputText v-model="search.value" placeholder="Keyword Search" class="pl-10 font-normal" />
                </div>
            </div>
        </template>
        <Column field="name" header="Name"></Column>
        <!-- <Column field="name" header="Lessons"></Column> -->
        <Column header="Homeworks">
            <template #body="slotProps">
                <Tag :value="slotProps.data.homework_count" severity="secondary" />
            </template>
        </Column>
        <Column field="teacher_name" header="Faculty"></Column>
        <Column header="">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <Button size="small" outlined>View</Button>
                    <Button size="small" outlined severity="danger">Delete</Button>
                </div>
            </template>
        </Column>
    </DataTable>
</template>

<style scoped></style>
