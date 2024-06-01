<script setup lang="ts">
import { onMounted } from 'vue';
import { useSubjectStore } from '../../stores/subject';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';


</script>
<template>
    <DataTable :value="[]" tableStyle="min-width: 10rem" paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <InputText v-model="search" placeholder="Search Students" class="font-normal" />
                </div>
            </div>
        </template>
        <Column field="name" header="Student"></Column>
        <!-- <Column field="name" header="Lessons"></Column> -->
        <Column header="Homeworks done">
            <template #body="slotProps">
                <Tag :value="slotProps.data.homework_count" severity="secondary" />
            </template>
        </Column>
        <Column field="score" header="Score"></Column>
        <Column header="">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <!-- <Button size="small" @click="goToViewSubject(slotProps.data.subject_id)" label="View"></Button>
                    <Button size="small" text severity="danger" label="Delete"></Button> -->
                </div>
            </template>
        </Column>
    </DataTable>
</template>

<style scoped></style>