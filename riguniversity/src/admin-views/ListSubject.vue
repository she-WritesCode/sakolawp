<script setup lang="ts">
import { onMounted } from "vue";
import { useSubjectStore } from "../stores/subject";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import TabMenu from 'primevue/tabmenu';
import Skeleton from 'primevue/skeleton';
import Dialog from 'primevue/dialog';
import { ref } from "vue";
import Homeworks from '../components/subjects/Homeworks.vue'
import Lessons from '../components/subjects/Lessons.vue'
import Students from '../components/subjects/Students.vue'
import AddSubject from '../components/subjects/AddSubject.vue'
const tabs = {
    homeworks: Homeworks,
    lessons: Lessons,
    students: Students,
}
const currentTab = ref<keyof typeof tabs>("lessons")
const items = ref([
    {
        label: 'Lessons',
        command: () => {
            currentTab.value = 'lessons'
            console.log(currentTab.value)
        }
    },
    {
        label: 'Homeworks',
        command: () => {
            currentTab.value = 'homeworks'
            console.log(currentTab.value)
        }
    },
    {
        label: 'Students',
        command: () => {
            currentTab.value = 'students'
            console.log(currentTab.value)
        }
    },
]);

const { subjects, fetchSubjects, search, goToViewSubject, subjectId, currentSubject, getOneSubject, loading, showAddFrom, goToAddForm } = useSubjectStore();

onMounted(() => {
    if (subjectId) {
        getOneSubject(subjectId)
    } else {
        fetchSubjects();
    }
});

const goBack = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('subject_id')
    url.searchParams.delete('homework_id')
    window.location.href = url.toString()
}
</script>

<template>
    <!-- Loading Indicator -->
    <div v-if="loading">
        <div class="rounded-lg dark:border-surface-700 bg-surface-0 dark:bg-surface-800 mb-4 p-4">
            <div class="flex mb-4">
                <Skeleton shape="circle" size="4rem" class="mr-2"></Skeleton>
                <div>
                    <Skeleton width="10rem" class="mb-2"></Skeleton>
                    <Skeleton width="5rem" class="mb-2"></Skeleton>
                    <Skeleton height=".5rem"></Skeleton>
                </div>
            </div>
            <Skeleton width="100%" height="150px"></Skeleton>
            <div class="flex justify-between mt-4">
                <Skeleton width="4rem" height="2rem"></Skeleton>
                <Skeleton width="4rem" height="2rem"></Skeleton>
            </div>
        </div>
    </div>
    <template v-else>
        <!-- Subject List -->
        <div v-if="!subjectId" class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Subjects</h3>
            </div>
            <DataTable :value="subjects" tableStyle="min-width: 10rem" class="border-0" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header class="border-t-0">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="search" placeholder="Search Subjects" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToAddForm" size="small" label="Add Homework"></Button>
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
                            <Button size="small" text severity="danger" label="Delete"></Button>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Dialog v-model:visible="showAddFrom" modal header="Add Subject" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <p class="mb-5">
                    <AddSubject></AddSubject>
                </p>
            </Dialog>
        </div>
        <!-- Single Subject -->
        <div v-else>
            <div class="mb-4"><Button @click="goBack" label="Back" outline severity="secondary"></Button></div>
            <div class="p-4 md:p-8 lg:p-12 bg-primary-700 text-white rounded flex flex-col gap-2 mb-4">

                <div class="flex items-center gap-2">
                    <h3 class="text-xl md:text-2xl text-white mb-2">{{ currentSubject?.name }}</h3>
                    <Button text class=" bg-white hover:bg-primary-50" label="Edit Subject"></Button>
                </div>
                <div class="flex gap-2">
                    <span>{{ currentSubject?.lesson_count }} Lesson(s)</span> |
                    <span>{{ currentSubject?.homework_count }} Homework(s)</span>
                </div>
                <div><b>Faculty:</b> {{ currentSubject?.teacher_name }}</div>

            </div>
            <TabMenu size="large" class="w-full border-0 mb-4" :model="items" />
            <div class="border-0">
                <component :is="tabs[currentTab]" />
            </div>
        </div>
    </template>
</template>

<style scoped></style>
