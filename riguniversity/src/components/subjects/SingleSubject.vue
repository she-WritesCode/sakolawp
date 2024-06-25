<script setup lang="ts">
import { onMounted } from "vue";
import { useSubjectStore } from "../../stores/subject";
import Button from 'primevue/button';
import TabMenu from 'primevue/tabmenu';
import { ref } from "vue";
import HomeworkList from '../subjects/HomeworkList.vue'
import Lessons from '../subjects/SubjectLessons.vue'
import Students from '../subjects/Students.vue'
import EditSubject from '../subjects/EditSubject.vue'
import { useHomeworkStore } from "../../stores/homework";
import Toast from "primevue/toast";
const tabs = {
    homeworks: HomeworkList,
    lessons: Lessons,
    students: Students,
    editSubject: EditSubject,
}
const currentTab = ref<keyof typeof tabs>("homeworks")
const items = ref([
    {
        label: 'Homeworks',
        command: () => {
            currentTab.value = 'homeworks'
        }
    },
    {
        label: 'Lessons',
        command: () => {
            currentTab.value = 'lessons'
        }
    },
]);

const {
    subjectId,
    currentSubject,
    getOneSubject,
} = useSubjectStore();
const { showAddForm: showHomeworkAddForm, showViewScreen: showViewHomeworkScreen, currentHomework } = useHomeworkStore();

onMounted(() => {
    if (subjectId) {
        getOneSubject(subjectId)
    }
});

</script>

<template>
    <Toast position="bottom-center" />
    <!-- Loading Indicator -->

    <!-- Subject List -->
    <div v-if="!subjectId" class="border-0">
        <div>Subject not found</div>

    </div>
    <!-- Single Subject -->
    <div v-else>
        <!-- Breadcrumb -->
        <div class="mb-4 px-2 text-sm text-surface-500">
            <a href="/wp-admin/edit.php?post_type=sakolawp-course">Courses</a>
            <template v-if="currentSubject">
                > <a :class="!currentHomework ? 'text-surface-900' : ''"
                    :href="`/wp-admin/edit.php?post_type=sakolawp-course&subject_id=${currentSubject?.subject_id}`">{{
        currentSubject?.name }}</a>
                <template v-if="currentHomework">
                    > <a
                        :href="`/wp-admin/edit.php?post_type=sakolawp-course&subject_id=${currentSubject?.subject_id}`">Homeworks</a>
                </template>
            </template>
            <template v-if="currentHomework">
                > <a class="text-surface-900" href="#">{{ currentHomework?.title }}</a>
            </template>
        </div>
        <div v-if="showHomeworkAddForm || showViewHomeworkScreen">
        </div>
        <template v-else>
            <!-- <div class="mb-4"><Button @click="goBack" label="Back" outlined severity="secondary"></Button></div> -->
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
            <div class="mb-2 py-1">
                <TabMenu size="large" class="w-full" :model="items" />
            </div>
        </template>
        <div class="border-0 mb-8">
            <component :is="tabs[currentTab]" />
        </div>
    </div>
</template>

<style scoped></style>
