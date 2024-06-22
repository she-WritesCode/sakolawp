<script setup lang="ts">
import { onMounted } from "vue";
import { useCourseStore } from "../../stores/course";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import AddCourse from './AddCourse.vue'
import LoadingIndicator from '../LoadingIndicator.vue'
import { ref } from "vue";


const {
    courses,
    fetchCourses,
    filter,
    goToViewCourse,
    courseId,
    loading,
    showAddForm,
    goToAddForm, deleteCourse,
} = useCourseStore();

onMounted(() => {
    if (!courseId) {
        fetchCourses();
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
function deleteACourse(id: string) {
    deleteCourse(id)
    closeDelete()
}
</script>

<template>
    <!-- Loading Indicator -->
    <div v-if="loading">
        <LoadingIndicator></LoadingIndicator>
    </div>
    <template v-else>
        <!-- Course List -->
        <div class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Courses</h3>
            </div>
            <DataTable :value="courses" tableStyle="min-width: 10rem" class="border-0" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="filter.search" placeholder="Search Courses" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToAddForm" size="small" label="Add Course"></Button>
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
                            <Button outlined size="small" @click="goToViewCourse(slotProps.data.course_id)"
                                label="View"></Button>
                            <Button size="small" @click="initDelete(slotProps.data.course_id)" text severity="danger"
                                label="Delete"></Button>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Dialog v-model:visible="showAddForm" modal header="Add Course" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <AddCourse></AddCourse>
            </Dialog>
            <Dialog v-model:visible="showDeleteDialog" modal header="Delete Course" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <p class="mb-5">
                    Are you sure you want to delete?
                </p>
                <div class="flex gap-2 justify-end">
                    <Button @click="closeDelete">No</Button>
                    <Button @click="deleteACourse(toBeDeleted as string)" outlined severity="danger">Yes</Button>
                </div>
            </Dialog>
        </div>
    </template>
</template>

<style scoped></style>
