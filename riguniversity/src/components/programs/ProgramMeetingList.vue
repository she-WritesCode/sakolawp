<script setup lang="ts">
import { onMounted } from "vue";
import { useprogramMeetingStore, type CalendarDay, type CreateprogramMeeting } from "../../stores/program-meeting";
import { useProgramStore } from "../../stores/program";
import { DateHelper } from "../../utils/date";
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import SelectButton from 'primevue/selectbutton';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import AddprogramMeeting from './AddprogramMeeting.vue'
import LoadingIndicator from '../LoadingIndicator.vue'
import { ref } from "vue";
import { Calendar as VCalendar } from 'v-calendar'
import { computed, watch } from "vue";


const { programId } = useProgramStore();
const {
    programMeetings,
    filter,
    goToEditprogramMeeting,
    programMeetingId,
    loading,
    showAddForm, showEditScreen,
    goToAddForm, deleteprogramMeeting, generateQRCode, currentprogramMeeting, getOneprogramMeeting
} = useprogramMeetingStore();

const editInitialValues = computed<CreateprogramMeeting & { ID: number } | undefined>(() => currentprogramMeeting.value ? ({
    ID: currentprogramMeeting.value!.ID!,
    title: currentprogramMeeting.value!.title || "",
    content: currentprogramMeeting.value!.content || "",
    meta: {
        _sakolawp_event_date: DateHelper.toSimpleBackendDateString(currentprogramMeeting.value!.meta!._sakolawp_event_date![0] || new Date()),
        _sakolawp_event_date_clock: currentprogramMeeting.value!.meta!._sakolawp_event_date_clock![0] || DateHelper.toSimpleBackendTimeString(new Date()),
        _sakolawp_event_class_id: currentprogramMeeting.value!.meta!._sakolawp_event_class_id![0] || programId,
        _sakolawp_event_location: currentprogramMeeting.value!.meta!._sakolawp_event_location![0] || "",
    },
} as CreateprogramMeeting & { ID: number }) : undefined)

const {
    programMeetings: allprogramMeetings,
    filter: allprogramMeetingsFilter,
} = useprogramMeetingStore('allprograms');

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
    deleteprogramMeeting(id)
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
    ...allprogramMeetings.value.map(meeting => ({
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
    filter.meta_query = [{ key: '_sakolawp_event_date', value: DateHelper.toSimpleBackendDateString(day.date), compare: '=' }]
    selectedDate.value = day.date
}

watch(displayView, (value) => {
    if (value == 'calendar') {
        filter.meta_query = [{ key: '_sakolawp_event_date', value: DateHelper.toSimpleBackendDateString(selectedDate.value), compare: '=' }]
    }
    if (value == 'list') {
        filter.meta_query = []
    }
})

onMounted(() => {
    filter.class_id = programId || ''
    filter.meta_query = [{ key: '_sakolawp_event_date', value: DateHelper.toSimpleBackendDateString(selectedDate.value), compare: '=' }]
    allprogramMeetingsFilter.class_id = programId || ''
    if (programMeetingId) getOneprogramMeeting(programMeetingId!)
});


</script>

<template>
    <!-- Meeting List -->
    <div class="border-0">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <div class="order-1">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Meetings</h3>
            </div>
            <div class="order-3 md:order-2" v-if="displayView == 'calendar'">
                <SelectButton v-model="calenderView" class="font-normal capitalize" :options="['weekly', 'monthly']" />
            </div>
            <div class="order-2 md:order-3">
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
            <TransitionGroup v-else name="slide-fade">
                <template v-if="!programMeetings.length">
                    <div class="text-base py-10 text-center">
                        No meetings for {{ DateHelper.formatDate(selectedDate) }}
                    </div>
                </template>
                <template v-else>
                    <div v-for="(meeting, index) in programMeetings" :key="index"
                        class="card flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <div class="text-base font-bold mb-2">
                                {{ meeting.title }}
                                <Tag :value="`0 Attendees`" severity="success" />
                                <Tag :value="`Upcoming`" severity="warning" />
                            </div>
                            <div class="flex flex-wrap gap-4 mb-2">
                                <div class="">
                                    <i class="pi pi-calendar"></i>
                                    {{ DateHelper.formatDate(meeting.meta!._sakolawp_event_date[0]) }}
                                    {{ DateHelper.formatTime(`${meeting.meta!._sakolawp_event_date[0]}
                                    ${meeting.meta!._sakolawp_event_date_clock[0]}`) }}
                                </div>
                                <div class="">
                                    <i class="pi pi-map-marker"></i>
                                    {{ meeting.meta!._sakolawp_event_location?.[0] }}
                                </div>
                                <div class="italic text sm">
                                    <i class="pi pi-user"></i>
                                    Added by: {{ meeting.author }}
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button size="small" outlined @click="goToEditprogramMeeting(meeting.ID!)"
                                label="Edit"></Button>

                            <Button @click="generateQRCode(meeting.ID!)" size="small" text
                                label="Download QR Code"></Button>
                        </div>
                    </div>
                </template>
            </TransitionGroup>
        </div>

        <Dialog v-model:visible="showAddForm" modal header="Add Meeting" :style="{ width: '30rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <AddprogramMeeting :programId="programId!"></AddprogramMeeting>
        </Dialog>
        <Dialog v-model:visible="showEditScreen" modal header="Edit Meeting" :style="{ width: '30rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <AddprogramMeeting :programId="programId!" :initialValues="editInitialValues"></AddprogramMeeting>
        </Dialog>
        <Dialog v-model:visible="showDeleteDialog" modal header="Remove Meeting from program"
            :style="{ width: '30rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <p class="mb-5">
                Are you sure you want to remove this meeting from this program? All student progress would be lost.
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
    @apply border border-surface-200 border-l-2 border-l-primary-500 rounded-md bg-surface-0 min-w-0 m-0 p-4 max-w-full w-full;
}

.card .title {
    @apply text-2xl font-bold flex gap-4 items-baseline text-primary-900;
}

.card .content {
    @apply text-sm uppercase;
}
</style>
