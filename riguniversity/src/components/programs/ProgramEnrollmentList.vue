<script setup lang="ts">
import { onMounted } from "vue";
import { useprogramEnrollmentStore } from "../../stores/program-enrollment";
import { useProgramStore } from "../../stores/program";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Avatar from 'primevue/avatar';
// import AddEnrollment from './AddEnrollment.vue'
import LoadingIndicator from '../LoadingIndicator.vue'
import { ref } from "vue";


const {
    programEnrollments,
    fetchprogramEnrollments,
    filter,
    goToViewprogramEnrollment,
    programEnrollmentId,
    loading,
    showAddForm,
    goToAddForm, deleteprogramEnrollment,
} = useprogramEnrollmentStore();
const {
    programId
} = useProgramStore();

onMounted(() => {
    if (!programEnrollmentId) {
        filter.class_id = programId || ''
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
function deleteAEnrollment(id: string) {
    deleteprogramEnrollment(id)
    closeDelete()
}
function getInitials(name: string) {
    return name.split(" ").map(str => str[0]).join("")
}
</script>

<template>
    <!-- Loading Indicator -->
    <div v-if="loading.list">
        <LoadingIndicator></LoadingIndicator>
    </div>
    <template v-else>
        <!-- Enrollment List -->
        <div class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Enrollments</h3>
            </div>
            <DataTable :value="programEnrollments" tableStyle="min-width: 10rem" class="border-0" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="filter.search" placeholder="Search Enrollments" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToAddForm" size="small" label="Add Enrollment"></Button>
                        </div>
                    </div>
                </template>
                <Column header="Student">
                    <template #body="slotProps">
                        <div class="flex gap-2 items-center">
                            <Avatar :label="getInitials(slotProps.data.student_name)" />
                            <div>
                                <div>{{ slotProps.data.student_name }}</div>
                                <div>{{ slotProps.data.enroll_code }}</div>
                                <div>
                                    <a class="text-primary-500 underline"
                                        :href="`mailto:${slotProps.data.student_email}`">Email</a>
                                </div>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column header="Grouping">
                    <template #body="slotProps">
                        <div>{{ slotProps.data.section_name }}</div>
                        <div>{{ slotProps.data.accountability_name }}</div>
                    </template>
                </Column>
                <Column header="Status" class="text-center">
                    <template #body>
                        <Tag value="Active" severity="success" />
                    </template>
                </Column>
                <Column header="Added at">
                    <template #body="slotProps">
                        {{ slotProps.data.created_at }}
                    </template>
                </Column>
                <Column header="">
                    <template #body="slotProps">
                        <div class="flex gap-2 text-sm">
                            <Button outlined size="small" @click="goToViewprogramEnrollment(slotProps.data.ID)"
                                label="View"></Button>
                            <Button size="small" @click="initDelete(slotProps.data.ID)" text severity="danger"
                                label="Delete"></Button>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Dialog v-model:visible="showAddForm" modal header="Add Enrollment" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <!-- <AddEnrollment></AddEnrollment> -->
                add enrollment form
            </Dialog>
            <Dialog v-model:visible="showDeleteDialog" modal header="Remove Enrollment from program"
                :style="{ width: '30rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <p class="mb-5">
                    Are you sure you want to remove this enrollment from this program? All student progress would be
                    lost.
                </p>
                <div class="flex gap-2 justify-end">
                    <Button @click="closeDelete">No</Button>
                    <Button @click="deleteAEnrollment(toBeDeleted as string)" outlined severity="danger">Yes</Button>
                </div>
            </Dialog>
        </div>
    </template>
</template>

<style scoped></style>
