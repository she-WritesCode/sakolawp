<script setup lang="ts">
import { onMounted } from "vue";
import { useSubjectStore } from "../stores/subject";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import TabMenu from 'primevue/tabmenu';
import Dialog from 'primevue/dialog';
import { ref } from "vue";
import Homeworks from '../components/subjects/Homeworks.vue'
import Lessons from '../components/subjects/Lessons.vue'
import Students from '../components/subjects/Students.vue'
import EditSubject from '../components/subjects/EditSubject.vue'
import AddSubject from '../components/subjects/AddSubject.vue'
import LoadingIndicator from "../components/LoadingIndicator.vue";
const tabs = {
    homeworks: Homeworks,
    lessons: Lessons,
    students: Students,
    editSubject: EditSubject,
}
const currentTab = ref<keyof typeof tabs>("lessons")
const items = ref([
    // {
    //     label: 'Lessons',
    //     command: () => {
    //         currentTab.value = 'lessons'
    //     }
    // },
    {
        label: 'Homeworks',
        command: () => {
            currentTab.value = 'homeworks'
        }
    },
    {
        label: 'Edit Subject',
        command: () => {
            currentTab.value = 'editSubject'
        }
    },
    // {
    //     label: 'Students',
    //     command: () => {
    //         currentTab.value = 'students'
    //     }
    // },
]);

const {
    subjects,
    fetchSubjects,
    search,
    goToViewSubject,
    subjectId,
    currentSubject,
    getOneSubject,
    loading,
    showAddForm,
    goToAddForm, deleteSubject,
} = useSubjectStore();

onMounted(() => {
    if (subjectId) {
        getOneSubject(subjectId)
    } else {
        fetchSubjects();
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
        <div v-if="!subjectId" class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Subjects</h3>
            </div>
            <DataTable :value="subjects" tableStyle="min-width: 10rem" class="border-0" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="search" placeholder="Search Subjects" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToAddForm" size="small" label="Add Subject"></Button>
                        </div>
                    </div>
                </template>
                <Column field="name" header="Name"></Column>
                <Column header="Lessons" class="text-center">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.lesson_count" severity="secondary" />
                    </template>
                </Column>
                <Column header="Homeworks" class="text-center">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.homework_count" severity="secondary" />
                    </template>
                </Column>
                <Column field="teacher_name" header="Faculty"></Column>
                <Column header="">
                    <template #body="slotProps">
                        <div class="flex gap-2 text-sm">
                            <Button size="small" @click="goToViewSubject(slotProps.data.subject_id)"
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
            <Dialog v-model:visible="showDeleteDialog" modal header="Delete Subject" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <p class="mb-5">
                    Are you sure you want to delete?
                </p>
                <div class="flex gap-2 justify-end">
                    <Button @click="closeDelete">No</Button>
                    <Button @click="deleteASubject(toBeDeleted as string)" outlined severity="danger">Yes</Button>
                </div>
            </Dialog>
        </div>
        <!-- Single Subject -->
        <div v-else>
            <div class="mb-4"><Button @click="goBack" label="Back" outline severity="secondary"></Button></div>
            <div class="p-4 md:p-8 lg:p-12 bg-primary-700 text-white rounded flex flex-col gap-2 mb-4">

                <div class="flex items-center gap-2">
                    <h3 class="text-xl md:text-2xl text-white mb-2">{{ currentSubject?.name }}</h3>
                    <Button text class=" bg-white hover:bg-primary-50" @click="currentTab = 'editSubject'"
                        label="Edit Subject"></Button>
                </div>
                <div class="flex gap-2">
                    <!-- <span>{{ currentSubject?.lesson_count }} Lesson(s)</span> | -->
                    <span>{{ currentSubject?.homework_count }} Homework(s)</span>
                </div>
                <div><b>Faculty:</b> {{ currentSubject?.teacher_name }}</div>

            </div>
            <TabMenu size="large" class="w-full border-0 mb-4" :model="items" />
            <div class="border-0 mb-8">
                <component :is="tabs[currentTab]" />
            </div>
        </div>
    </template>
</template>

<style scoped></style>
