<script setup lang="ts">
import { onMounted } from "vue";
import { useCohortStore } from "../stores/cohort";
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import TabMenu from 'primevue/tabmenu';
import DataView from 'primevue/dataview';
import Dialog from 'primevue/dialog';
import { ref } from "vue";
import HomeworkList from '../components/subjects/HomeworkList.vue'
import CohortSubjectList from '../components/cohorts/CohortSubjectList.vue'
import CohortMeetingList from '../components/cohorts/CohortMeetingList.vue'
import CohortGroupList from '../components/cohorts/CohortGroupList.vue'
// import EditCohort from '../components/cohorts/EditCohort.vue'
// import AddCohort from '../components/cohorts/AddCohort.vue'
import LoadingIndicator from "../components/LoadingIndicator.vue";
import { useSubjectStore } from "../stores/subject";
import Toast from "primevue/toast";

const tabs = {
    subjects: CohortSubjectList,
    groupings: CohortGroupList,
    enrollments: HomeworkList,
    editCohort: HomeworkList,
    meetings: CohortMeetingList,
}
const currentTab = ref<keyof typeof tabs | 'statistics'>("statistics")
const items = ref([
    {
        label: 'Overview',
        command: () => {
            currentTab.value = 'statistics'
        }
    },
    {
        label: 'Subjects',
        command: () => {
            currentTab.value = 'subjects'
        }
    },
    {
        label: 'Meetings',
        command: () => {
            currentTab.value = 'meetings'
        }
    },
    {
        label: 'Cohort Groups',
        command: () => {
            currentTab.value = 'groupings'
        }
    },
    {
        label: 'Enrollments',
        command: () => {
            currentTab.value = 'enrollments'
        }
    },
    {
        label: 'Edit Cohort',
        command: () => {
            currentTab.value = 'editCohort'
        }
    },
]);

const {
    cohorts,
    fetchCohorts,
    filter,
    goToViewCohort,
    cohortId,
    currentCohort,
    getOneCohort,
    loading,
    showAddForm,
    goToAddForm, deleteCohort,
} = useCohortStore();
const { showAddForm: showSubjectAddForm, currentSubject } = useSubjectStore();

onMounted(() => {
    if (cohortId) {
        getOneCohort(cohortId)
    } else {
        fetchCohorts();
    }
});

const goBack = () => {
    window.history.back()
}

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
function deleteACohort(id: string) {
    deleteCohort(id)
    closeDelete()
}
</script>

<template>
    <Toast position="bottom-center" />
    <!-- Cohort List -->
    <div v-if="!cohortId" class="border-0">
        <div class="px-2 pb-4">
            <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Cohorts</h3>
        </div>
        <DataView dataKey="class_id" :value="cohorts" paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <InputText v-model="filter.search" placeholder="Search Cohorts" class="font-normal" />
                    </div>
                    <div class="">
                        <Button @click="goToAddForm" size="small" label="+ Add Cohort"></Button>
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
                            <div @click="goToViewCohort(item.class_id)"
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
                                <Button outlined size="small" @click="goToViewCohort(item.class_id)" label="View"
                                    class="w-full"></Button>
                                <!-- <Button size="small" @click="initDelete(slotProps.data.class_id)" text severity="danger"
                                label="Delete"></Button> -->
                            </div>
                        </div>
                        <Button @click="goToAddForm" key="add_button" outlined class="border-dashed">
                            <div class="text-lg mb-4">
                                + Add Cohort
                            </div>
                        </Button>
                    </template>
                </div>
            </template>
        </DataView>

        <Dialog v-model:visible="showAddForm" modal header="Add Cohort" :style="{ width: '30rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <!-- <AddCohort></AddCohort> -->
        </Dialog>
        <Dialog v-model:visible="showDeleteDialog" modal header="Delete Cohort" :style="{ width: '30rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <p class="mb-5">
                Are you sure you want to delete?
            </p>
            <div class="flex gap-2 justify-end">
                <Button @click="closeDelete">No</Button>
                <Button @click="deleteACohort(toBeDeleted as string)" outlined severity="danger">Yes</Button>
            </div>
        </Dialog>
    </div>
    <!-- Single Cohort -->
    <div v-else>
        <!-- Breadcrumb -->
        <div class="mb-4 px-2 text-sm text-surface-500">
            <a href="/wp-admin/admin.php?page=sakolawp-manage-class">Cohorts</a>
            <template v-if="currentCohort">
                > <a :class="!currentSubject ? 'text-surface-900' : ''"
                    :href="`/wp-admin/admin.php?page=sakolawp-manage-class&class_id=${currentCohort?.class_id}`">{{
        currentCohort?.name }}</a>
                <template v-if="currentSubject">
                    > <a
                        :href="`/wp-admin/admin.php?page=sakolawp-manage-class&class_id=${currentCohort?.class_id}`">Subjects</a>
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
                    <h3 class="text-2xl md:text-2xlmb-2">{{ currentCohort?.name }}</h3>
                    <Button text size="small" outlined class="" @click="currentTab = 'editCohort'"
                        label="Edit Cohort"></Button>
                </div>
            </div>
            <div class="my-2 py-1">
                <TabMenu size="large" class="w-full" :model="items" />
            </div>
        </template>
        <div class="border-0 mb-8">
            <div v-if="currentTab == 'statistics'">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div class="card">
                        <div class="title">{{ currentCohort?.student_count }} </div>
                        <div class="content">Students</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentCohort?.section_count }}</div>
                        <div class="content">Parent Groups</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentCohort?.accountability_count }}</div>
                        <div class="content">Accountability Groups</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentCohort?.subject_count }}</div>
                        <div class="content">Subjects</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentCohort?.event_count }}</div>
                        <div class="content">Events</div>
                    </div>
                    <div class="card">
                        <div class="title">{{ currentCohort?.teacher_count }}</div>
                        <div class="content">Faculties</div>
                    </div>
                </div>
            </div>
            <component v-else :is="tabs[currentTab]" />
        </div>
    </div>
</template>

<style scoped>
.card {
    @apply border-x-0 border-b-0 border-t-2 border-primary-500 rounded-md bg-surface-0 shadow min-w-0 m-0 p-4;
}

.card .title {
    @apply text-2xl font-bold;
}

.card .content {
    @apply text-sm uppercase;
}
</style>
