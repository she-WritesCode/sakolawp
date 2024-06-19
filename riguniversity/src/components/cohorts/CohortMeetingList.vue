<script setup lang="ts">
import { onMounted } from "vue";
import { useCohortMeetingStore, type CalendarDay } from "../../stores/cohort-meeting";
import { useCohortStore } from "../../stores/cohort";
import { DateHelper } from "../../utils/date";
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import SelectButton from 'primevue/selectbutton';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
// import AddMeeting from './AddMeeting.vue'
import LoadingIndicator from '../LoadingIndicator.vue'
import { ref } from "vue";
import { Calendar as VCalendar } from 'v-calendar'
import { computed, watch } from "vue";


const {
    cohortMeetings,
    filter,
    goToViewCohortMeeting,
    cohortMeetingId,
    loading,
    showAddForm,
    goToAddForm, deleteCohortMeeting, generateQRCode
} = useCohortMeetingStore();

const {
    cohortMeetings: allCohortMeetings,
    filter: allCohortMeetingsFilter,
} = useCohortMeetingStore('allcohorts');
const {
    cohortId
} = useCohortStore();

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

const displayView = ref<'calendar' | 'list'>('calendar')
const calenderView = ref<'weekly' | 'monthly'>('weekly')

const selectedDate = ref(new Date())

const attrs = computed<Partial<{
    key: string | number;
    hashcode: string;
    customData: any;
    order: number;
    highlight: boolean;
    pinPage: boolean;
    dates: Date;
}>[]>(() => [
    {
        key: 'selected',
        highlight: true,
        dates: selectedDate.value,
    },
    // Attributes for todos
    ...allCohortMeetings.value.map(meeting => ({
        key: meeting.ID,
        dates: new Date(`${meeting.meta!._sakolawp_event_date[0]} ${meeting.meta!._sakolawp_event_date_clock[0]}`),
        dot: {
            color: 'red',
            ...(meeting.author && { class: 'opacity-75' }),
        },
        popover: {
            label: meeting.title,
        },
    })),
]);

const onDayClick = (day: CalendarDay, event: MouseEvent) => {
    filter.meta_query = [{ key: '_sakolawp_event_date', value: DateHelper.toSimpleBeDateString(day.date), compare: '=' }]
    selectedDate.value = day.date
}

watch(displayView, (value) => {
    if (value == 'calendar') {
        filter.meta_query = [{ key: '_sakolawp_event_date', value: DateHelper.toSimpleBeDateString(selectedDate.value), compare: '=' }]
    }
    if (value == 'list') {
        filter.meta_query = []
    }
})

onMounted(() => {
    if (!cohortMeetingId) {
        filter.class_id = cohortId || ''
        filter.meta_query = [{ key: '_sakolawp_event_date', value: DateHelper.toSimpleBeDateString(selectedDate.value), compare: '=' }]
    }
    allCohortMeetingsFilter.class_id = cohortId || ''
});


</script>

<template>
    <!-- Meeting List -->
    <div class="border-0">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <div class="mb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Meetings</h3>
            </div>
            <div>
                <template v-if="displayView == 'calendar'">
                    <SelectButton v-model="calenderView" class="font-normal capitalize"
                        :options="['weekly', 'monthly']" />
                </template>
            </div>
            <div class="">
                <SelectButton v-model="displayView" class="font-normal capitalize" :options="['calendar', 'list']" />
            </div>
        </div>

        <template v-if="displayView == 'calendar'">
            <VCalendar expanded borderless transparent :view="calenderView" :color="'blue'" :attributes="(attrs as any)"
                @dayclick="onDayClick">
            </VCalendar>
        </template>

        <div class="mt-4 grid grid-cols-1 gap-4 w-full">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <InputText v-model="filter.search" placeholder="Search Meetings" class="font-normal" />
                </div>
                <div class="">
                    <Button @click="goToAddForm" size="small" label="Add Meeting"></Button>
                </div>
            </div>
            <!-- Loading Indicator -->
            <div v-if="loading.list">
                <LoadingIndicator></LoadingIndicator>
            </div>
            <template v-else>
                <template v-if="!cohortMeetings.length">
                    <div class="text-base py-10 text-center">
                        No meetings for {{ DateHelper.formatDate(selectedDate) }}
                    </div>
                </template>
                <template v-else>
                    <div v-for="(meeting, index) in cohortMeetings" :key="index"
                        class="card flex items-center justify-between gap-4">
                        <div>
                            <div class="text-base">
                                {{ meeting.title }}
                                <Tag :value="meeting.author" severity="secondary" />
                            </div>
                            <div class="">
                                {{ DateHelper.relativeTime(meeting.meta!._sakolawp_event_date[0]) }}
                                {{ DateHelper.formatTime(`${meeting.meta!._sakolawp_event_date[0]}
                                ${meeting.meta!._sakolawp_event_date_clock[0]}`) }}
                            </div>
                        </div>
                        <div>
                            <Button size="small" outlined @click="goToViewCohortMeeting(meeting.ID!)"
                                label="Edit"></Button>

                            <Button @click="generateQRCode(meeting.ID!)" size="small" text
                                label="Download QR Code"></Button>
                        </div>
                    </div>
                </template></template>

        </div>

        <Dialog v-model:visible="showAddForm" modal header="Add Meeting" :style="{ width: '30rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <!-- <AddMeeting></AddMeeting> -->
            add meeting form
        </Dialog>
        <Dialog v-model:visible="showDeleteDialog" modal header="Remove Meeting from Cohort" :style="{ width: '30rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
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

<style scoped>
.card {
    @apply border border-surface-200 rounded-md bg-surface-0 min-w-0 m-0 p-4 max-w-full w-full;
}

.card .title {
    @apply text-2xl font-bold flex gap-4 items-baseline text-primary-900;
}

.card .content {
    @apply text-sm uppercase;
}
</style>
