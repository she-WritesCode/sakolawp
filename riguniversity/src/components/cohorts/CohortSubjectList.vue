<script setup lang="ts">
import { onMounted } from "vue";
import { useCohortSubjectStore } from "../../stores/cohort-subject";
import { useCohortStore } from "../../stores/cohort";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
// import AddSubject from './AddSubject.vue'
import LoadingIndicator from '../LoadingIndicator.vue'
import { ref } from "vue";


const {
    cohortSubjects,
    filter,
    goToViewCohortSubject,
    cohortSubjectId,
    loading,
} = useCohortSubjectStore();
const {
    cohortId, goToEditCohort
} = useCohortStore();

onMounted(() => {
    if (!cohortSubjectId) {
        filter.class_id = cohortId || ''
    }
});

</script>

<template>
    <!-- Loading Indicator -->
    <div v-if="loading.list">
        <LoadingIndicator></LoadingIndicator>
    </div>
    <template v-else>
        <!-- Subject List -->
        <div class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Subjects</h3>
            </div>
            <DataTable :value="cohortSubjects" tableStyle="min-width: 10rem" class="border-0" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="filter.search" placeholder="Search Subjects" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToEditCohort(cohortId as string)" size="small"
                                label="Change Subjects"></Button>
                        </div>
                    </div>
                </template>
                <Column field="name" header="Name"></Column>
                <!-- <Column header="Lessons" class="text-center">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.lesson_count" severity="secondary" />
                    </template>
                </Column> -->
                <Column header="Homeworks" class="text-center">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.homework_count" severity="secondary" />
                    </template>
                </Column>
                <Column field="teacher_name" header="Faculty"></Column>
                <Column header="">
                    <template #body="slotProps">
                        <div class="flex gap-2 text-sm">
                            <Button outlined size="small" @click="goToViewCohortSubject(slotProps.data.subject_id)"
                                label="View"></Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </template>
</template>

<style scoped></style>
