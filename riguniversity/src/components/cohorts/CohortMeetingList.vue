<script setup lang="ts">
import { onMounted } from "vue";
import { useCohortMeetingStore } from "../../stores/cohort-meeting";
import { useCohortStore } from "../../stores/cohort";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
// import AddMeeting from './AddMeeting.vue'
import LoadingIndicator from '../LoadingIndicator.vue'
import { ref } from "vue";


const {
    cohortMeetings,
    fetchCohortMeetings,
    filter,
    goToViewCohortMeeting,
    cohortMeetingId,
    loading,
    showAddForm,
    goToAddForm, deleteCohortMeeting,
} = useCohortMeetingStore();
const {
    cohortId
} = useCohortStore();

onMounted(() => {
    if (!cohortMeetingId) {
        filter.class_id = cohortId || ''
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
function deleteAMeeting(id: string) {
    deleteCohortMeeting(id)
    closeDelete()
}
</script>

<template>
    <!-- Loading Indicator -->
    <div v-if="loading.list">
        <LoadingIndicator></LoadingIndicator>
    </div>
    <template v-else>
        <!-- Meeting List -->
        <div class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Meetings</h3>
            </div>
            <DataTable :value="cohortMeetings" tableStyle="min-width: 10rem" class="border-0" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="filter.search" placeholder="Search Meetings" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToAddForm" size="small" label="Add Meeting"></Button>
                        </div>
                    </div>
                </template>
                <Column field="title" header="Name"></Column>
                <Column header="Date">
                    <template #body="slotProps">
                        {{ new Date(`${slotProps.data.meta._sakolawp_event_date[0]}
                        ${slotProps.data.meta._sakolawp_event_date_clock[0]}`).toLocaleString() }}
                    </template>
                </Column>
                <Column header="Grace period">
                    <template #body="slotProps">
                        {{ slotProps.data.meta._sakolawp_event_late_deadline[0] }} mins
                    </template>
                </Column>
                <Column header="Attendees" class="text-center">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.homework_count" severity="secondary" />
                    </template>
                </Column>
                <Column header="">
                    <template #body="slotProps">
                        <div class="flex gap-2 text-sm">
                            <Button outlined size="small" @click="goToViewCohortMeeting(slotProps.data.ID)"
                                label="View"></Button>
                            <Button size="small" @click="initDelete(slotProps.data.ID)" text severity="danger"
                                label="Delete"></Button>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Dialog v-model:visible="showAddForm" modal header="Add Meeting" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <!-- <AddMeeting></AddMeeting> -->
                add meeting form
            </Dialog>
            <Dialog v-model:visible="showDeleteDialog" modal header="Remove Meeting from Cohort"
                :style="{ width: '30rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <p class="mb-5">
                    Are you sure you want to remove this meeting from this cohort? All student progress would be lost.
                </p>
                <div class="flex gap-2 justify-end">
                    <Button @click="closeDelete">No</Button>
                    <Button @click="deleteAMeeting(toBeDeleted as string)" outlined severity="danger">Yes</Button>
                </div>
            </Dialog>
        </div>
    </template>
</template>

<style scoped></style>
