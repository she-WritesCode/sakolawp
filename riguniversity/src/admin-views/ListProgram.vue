<script setup lang="ts">
import { onMounted } from "vue";
import { useProgramStore } from "../stores/program";
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import TabMenu from 'primevue/tabmenu';
import DataView from 'primevue/dataview';
import Dialog from 'primevue/dialog';
import { ref } from "vue";
// import HomeworkList from '../components/subjects/HomeworkList.vue'
import ProgramSubjectList from '../components/programs/ProgramSubjectList.vue'
import ProgramMeetingList from '../components/programs/ProgramMeetingList.vue'
import ProgramGroupList from '../components/programs/ProgramGroupList.vue'
import ProgramEnrollmentList from '../components/programs/ProgramEnrollmentList.vue'
import EditProgram from '../components/programs/EditProgram.vue'
import AddProgram from '../components/programs/AddProgram.vue'
import LoadingIndicator from "../components/LoadingIndicator.vue";
import { useSubjectStore } from "../stores/subject";
import Toast from "primevue/toast";

const tabs = {
    subjects: ProgramSubjectList,
    groupings: ProgramGroupList,
    enrollments: ProgramEnrollmentList,
    editProgram: EditProgram,
    meetings: ProgramMeetingList,
}
const currentTab = ref<keyof typeof tabs | 'statistics'>(new URL(window.location.href).searchParams.get('tab') as keyof typeof tabs || "statistics")

const switchTabUrl = (tab: keyof typeof tabs | 'statistics') => {
    currentTab.value = tab
    const url = new URL(window.location.href)
    url.searchParams.set('tab', currentTab.value)
    window.history.pushState({}, document.title, url.toString())
}

const items = ref([
    {
        label: 'Overview',
        command: () => {
            switchTabUrl('statistics')
        }
    },
    {
        label: 'Schedule',
        command: () => {
            switchTabUrl('subjects')
        }
    },
    {
        label: 'Meetings',
        command: () => {
            switchTabUrl('meetings')
        }
    },
    {
        label: 'Program Groups',
        command: () => {
            switchTabUrl('groupings')
        }
    },
    {
        label: 'Enrollments',
        command: () => {
            switchTabUrl('enrollments')
        }
    },
    {
        label: 'Edit program',
        command: () => {
            switchTabUrl('editProgram')
        }
    },
]);

const {
    programs,
    fetchPrograms,
    filter,
    goToViewProgram,
    programId,
    currentProgram,
    getOneProgram,
    loading,
    showAddForm,
    goToAddForm,
    deleteProgram,
} = useProgramStore();
const { showAddForm: showSubjectAddForm, currentSubject } = useSubjectStore();

onMounted(() => {
    if (programId) {
        getOneProgram(programId)
    } else {
        fetchPrograms();
    }
});


</script>

<template>
    <Toast position="bottom-center" />
    <!-- program List -->
    <div v-if="!programId" class="border-0">
        <div class="px-2 pb-4">
            <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Programs</h3>
        </div>
        <DataView dataKey="class_id" :value="programs" paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <InputText v-model="filter.search" placeholder="Search programs" class="font-normal" />
                    </div>
                    <div class="">
                        <Button @click="goToAddForm" size="small" label="+ Add Program"></Button>
                    </div>
                </div>
            </template>
            <template #list="slotProps">
                <div class="grid grid-nogutter md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div v-if="loading.list">
                        <div v-for="i in [1, 2, 3, 4, 5, 6]" :key="i" class="col-12">
                            <LoadingIndicator></LoadingIndicator>
                        </div>
                    </div>
                    <template v-else>
                        <div v-for="(item, index) in slotProps.items" :key="index"
                            class="col-12 border border-surface-200 rounded-md hover:shadow-md p-4">
                            <div @click="goToViewProgram(item.class_id)"
                                class="text-lg mb-4 gap-2 flex items-center flex-wrap">
                                <span>{{ item.name }}</span>
                                <Tag :value="`${item.student_count} Students`" severity="warning" />
                            </div>
                            <div class="grid md:grid-cols-2 gap-2 mb-4">
                                <div>
                                    <Tag :value="item.section_count" severity="secondary" class="min-w-8" /> Parent
                                    Groups
                                </div>
                                <div>
                                    <Tag :value="item.accountability_count" severity="secondary" class="min-w-8" />
                                    Accountability Groups
                                </div>
                                <div>
                                    <Tag :value="item.subject_count" severity="secondary" class="min-w-8" /> Subjects
                                </div>
                                <div>
                                    <Tag :value="item.teacher_count" severity="secondary" class="min-w-8" /> Faculties
                                </div>
                            </div>
                            <div class="flex gap-2 text-sm">
                                <Button outlined size="small" @click="goToViewProgram(item.class_id)" label="View"
                                    class="w-full"></Button>
                                <!-- <Button size="small" @click="initDelete(slotProps.data.class_id)" text severity="danger"
                                label="Delete"></Button> -->
                            </div>
                        </div>
                        <Button @click="goToAddForm" key="add_button" outlined class="border-dashed min-h-44">
                            <div class="text-lg mb-4">
                                + Add program
                            </div>
                        </Button>
                    </template>
                </div>
            </template>
        </DataView>

        <Dialog v-model:visible="showAddForm" modal header="Add Program" :style="{ width: '30rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <AddProgram></AddProgram>
        </Dialog>
    </div>
    <!-- Single program -->
    <div v-else>
        <!-- Breadcrumb -->
        <div class="mb-4 px-2 text-sm text-surface-500">
            <a href="/wp-admin/admin.php?page=sakolawp-manage-class">programs</a>
            <template v-if="currentProgram">
                > <a :class="!currentSubject ? 'text-surface-900' : ''"
                    :href="`/wp-admin/admin.php?page=sakolawp-manage-class&class_id=${currentProgram?.class_id}`">{{
        currentProgram?.name }}</a>
                <template v-if="currentSubject">
                    > <a
                        :href="`/wp-admin/admin.php?page=sakolawp-manage-class&class_id=${currentProgram?.class_id}`">Subjects</a>
                </template>
            </template>
            <template v-if="currentSubject">
                > <a class="text-surface-900" href="#">{{ currentSubject?.name }}</a>
            </template>
        </div>
        <div v-if="showSubjectAddForm">
        </div>
        <template v-else>
            <div class="mb-4'">
                <div class="flex items-center gap-2">
                    <h3 class="text-2xl md:text-2xlmb-2">{{ currentProgram?.name }}</h3>
                    <Button text size="small" outlined class="" @click="switchTabUrl('editProgram')"
                        label="Edit program"></Button>
                </div>
            </div>
            <div class="my-2 py-1">
                <TabMenu size="large" class="w-full" :model="items" />
            </div>
        </template>
        <div class="border-0 mb-8">
            <div v-if="currentTab == 'statistics'">
                <div class="text-xl font-bold mb-4">Overview</div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div class="card">
                        <div class="title">{{ currentProgram?.student_count }} </div>
                        <div class="content">Students</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentProgram?.section_count }}</div>
                        <div class="content">Parent Groups</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentProgram?.accountability_count }}</div>
                        <div class="content">Accountability Groups</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentProgram?.subject_count }}</div>
                        <div class="content">Subjects</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentProgram?.event_count }}</div>
                        <div class="content">Events</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentProgram?.teacher_count }}</div>
                        <div class="content">Faculties</div>
                    </div>
                    <div class="card">
                        <div class="title">
                            <div>0</div>
                            <div class="text-xs">0% <span>^</span></div>
                        </div>
                        <div class="content">Active Students</div>
                    </div>
                </div>
            </div>
            <component v-else :is="tabs[currentTab]" />
        </div>
    </div>
</template>

<style scoped>
.card {
    @apply border-y-0 border-r-0 border-l-2 border-primary-500 rounded-md bg-surface-0 shadow min-w-0 m-0 p-4;
}

.card .title {
    @apply text-2xl font-bold flex gap-4 items-baseline text-primary-900;
}

.card .content {
    @apply text-sm uppercase;
}
</style>
