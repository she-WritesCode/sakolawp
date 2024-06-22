<script setup lang="ts">
import { onMounted } from "vue";
import { useCourseStore } from "../../stores/course";
import TabMenu from 'primevue/tabmenu';
import { ref } from "vue";
import HomeworkList from '../courses/HomeworkList.vue'
import LessonList from '../courses/LessonList.vue'
import { useHomeworkStore } from "../../stores/homework";
import { useLessonStore } from "../../stores/lesson";
const tabs = {
    homeworks: HomeworkList,
    lessons: LessonList,
}
const currentTab = ref<keyof typeof tabs>(new URL(window.location.href).searchParams.get('rig_tab') as keyof typeof tabs || "homeworks")


const switchTabUrl = (tab: keyof typeof tabs) => {
    currentTab.value = tab
    const url = new URL(window.location.href)
    url.searchParams.set('rig_tab', currentTab.value)
    window.history.pushState({}, document.title, url.toString())
}


const items = ref([
    {
        label: 'Homeworks',
        command: () => {
            switchTabUrl('homeworks')
        }
    },
    {
        label: 'Lessons',
        command: () => {
            switchTabUrl('lessons')
        }
    },
]);

const {
    courseId,
    currentCourse,
    getOneCourse,
} = useCourseStore();
const { showAddForm: showHomeworkAddForm, showViewScreen: showViewHomeworkScreen, currentHomework, filter } = useHomeworkStore();
const { showAddForm: showLessonAddForm, showViewScreen: showViewLessonScreen, currentLesson } = useLessonStore();

onMounted(() => {
    if (courseId) {
        filter.subject_id = courseId
        getOneCourse(courseId)
    }
});
</script>

<template>
    <!-- Course List -->
    <div v-if="!courseId" class="border-0">
        <div>Course not found</div>

    </div>
    <!-- Single Course -->
    <div v-else>
        <!-- Breadcrumb -->
        <div class="mb-4 px-2 text-sm text-surface-500">
            <a href="/wp-admin/edit.php?post_type=sakolawp-course">Courses</a>
            <template v-if="currentCourse">
                > <a :class="!currentHomework ? 'text-surface-900' : ''"
                    :href="`/wp-admin/post.php?&post=${currentCourse?.ID}&action=edit`">{{
        currentCourse?.title }}</a>
                <template v-if="currentHomework">
                    > <a :href="`/wp-admin/post.php?&post=${currentCourse?.ID}&action=edit&expanded=1`">Homeworks</a>
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
                    <h3 class="text-xl md:text-2xl text-white mb-2">{{ currentCourse?.title }}</h3>
                </div>
                <div class="flex items-center gap-2">
                    <p class="text-white mb-2">{{ currentCourse?.content }}</p>
                </div>
                <div class="flex gap-2">
                    <span>{{ currentCourse?.lesson_count }} Lesson(s)</span> |
                    <span>{{ currentCourse?.homework_count }} Homework(s)</span>
                </div>
                <div><b>Faculty:</b> {{ currentCourse?.author }}</div>

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
