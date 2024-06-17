<script setup lang="ts">
import { onMounted } from "vue";
import { useSubjectStore } from "../../stores/subject";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import AddSubject from './AddSubject.vue'
import { ref } from "vue";


const {
    subjects,
    fetchSubjects,
    filter,
    goToViewSubject,
    subjectId,
    loading,
    showAddForm,
    goToAddForm, deleteSubject,
} = useSubjectStore();

onMounted(() => {
    if (!subjectId) {
        fetchSubjects();
    }
});


const showDeleteDialog = ref(false)
const toBeDeleted = ref<string | null>(null)
function initDelete(id: string) {
    showDeleteDialog.value = true
    toBeDeleted.value = id
}
function closeDelete() {
    showDeleteDialog.value = false
    toBeDeleted.value = null
}
function deleteASubject(id: string) {
    deleteSubject(id)
    closeDelete()
}
</script>

<template>
    <!-- Loading Indicator -->
    <div v-if="loading">
        <LoadingIndicator></LoadingIndicator>
    </div>
    <template v-else>
        <!-- Subject List -->
        <div class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Subjects</h3>
            </div>
            <DataTable :value="subjects" tableStyle="min-width: 10rem" class="border-0" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="filter.search" placeholder="Search Subjects" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToAddForm" size="small" label="Add Subject"></Button>
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
                            <Button outlined size="small" @click="goToViewSubject(slotProps.data.subject_id)"
                                label="View"></Button>
                            <Button size="small" @click="initDelete(slotProps.data.subject_id)" text severity="danger"
                                label="Delete"></Button>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Dialog v-model:visible="showAddForm" modal header="Add Subject" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <AddSubject></AddSubject>
            </Dialog>
            <Dialog v-model:visible="showDeleteDialog" modal header="Remove Subject from Cohort" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <p class="mb-5">
                    Are you sure you want to remove this subject from this cohort? All student progress would be lost.
                </p>
                <div class="flex gap-2 justify-end">
                    <Button @click="closeDelete">No</Button>
                    <Button @click="deleteASubject(toBeDeleted as string)" outlined severity="danger">Yes</Button>
                </div>
            </Dialog>
        </div>
    </template>
</template>

<style scoped></style>
