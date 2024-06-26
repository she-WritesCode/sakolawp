<script setup lang="ts">
import { onMounted, computed, ref } from "vue";
import { useProgramSubjectStore } from "../../stores/program-subject";
import { useProgramStore } from "../../stores/program";
import DataView from 'primevue/dataview';
import Panel from 'primevue/panel';
import TabMenu from 'primevue/tabmenu';
// import TabView from 'primevue/tabview';
// import TabPanel from 'primevue/tabpanel';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import LoadingIndicator from '../LoadingIndicator.vue'
import ProgramSubjectHomeworkSchedule from './ProgramSubjectHomeworkSchedule.vue'
import ProgramSubjectLessonSchedule from './ProgramSubjectLessonSchedule.vue'


const {
    programSubjects,
    filter,
    programSubjectId,
    loading,
} = useProgramSubjectStore();
const {
    programId, goToEditProgram: goToEditProgram, currentProgram: currentProgram
} = useProgramStore();

onMounted(() => {
    if (!programSubjectId) {
        filter.class_id = programId || ''
    }
});

const getEditSubjectUrl = (subjectId: number | string) => {
    const url = new URL(window.location.href)
    url.pathname = '/wp-admin/post.php'
    url.search = ""
    url.searchParams.set('post', `${subjectId}`)
    url.searchParams.set('action', 'edit')
    return url.toString()
}

const tabs = {
    lessons: ProgramSubjectLessonSchedule,
    homeworks: ProgramSubjectHomeworkSchedule,
}
const currentTab = ref<keyof typeof tabs>(new URL(window.location.href).searchParams.get('sub_tab') as keyof typeof tabs || "homeworks")
const count = ref(0)
const switchTabUrl = (tab: keyof typeof tabs) => {
    count.value = 0
    currentTab.value = tab
    const url = new URL(window.location.href)
    url.searchParams.set('sub_tab', currentTab.value)
    window.history.pushState({}, document.title, url.toString())
}
const items = ref([
    {
        label: 'Assessments',
        key: 'homeworks',
        command: () => {
            switchTabUrl('homeworks')
        }
    },
    {
        label: 'Lessons',
        key: 'lessons',
        command: () => {
            switchTabUrl('lessons')
        }
    },
]);
const activeIndex = computed(() => items.value.findIndex(item => item.key == currentTab.value) ?? 0)


</script>

<template>
    <!-- Loading Indicator -->
    <div v-if="loading.list">
        <LoadingIndicator></LoadingIndicator>
    </div>
    <template v-else>
        <!-- Subject List -->
        <div class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Course Schedules</h3>
                <p>Click on + to manage the schedule of every course</p>
            </div>

            <DataView dataKey="class_id" :value="programSubjects" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="filter.search" placeholder="Search Courses" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToEditProgram(programId as string)" size="small"
                                label="Change Courses"></Button>
                        </div>
                    </div>
                </template>
                <template #list="slotProps">
                    <div class="grid grid-nogutter gap-4">
                        <div v-if="loading.list">
                            <div v-for="i in [1, 2, 3, 4, 5, 6]" :key="i" class="col-12">
                                <LoadingIndicator></LoadingIndicator>
                            </div>
                        </div>
                        <template v-else>
                            <Panel toggleable v-for="(item, index) in slotProps.items" :key="index" collapsed>
                                <template #header>
                                    <div>
                                        <div class="text-lg gap-2 flex items-center flex-wrap">
                                            <span>{{ item.title }}</span>
                                            <Tag :value="`${item.homework_count} Assessment`" severity="secondary" />
                                            <Tag :value="`${item.lesson_count} Lessons`" severity="secondary" />
                                        </div>
                                        <Tag :value="`Faculty: ${item.author}`" severity="secondary" />
                                        <a :href="getEditSubjectUrl(item.ID)"><Button link class="!py-0"
                                                label="Edit Course"></Button></a>
                                    </div>
                                </template>

                                <!-- <template #icons>
                                </template> -->
                                <TabMenu size="large" class="w-full" :activeIndex="activeIndex" :model="items" />
                                <div class="pt-8">
                                    <component :is="tabs[currentTab]" :key="index" :homeworks="item.homeworks"
                                        :subjectId="item.ID" :programId="(programId as string)"
                                        :dripMethod="currentProgram?.drip_method" :lessons="item.lessons" />
                                </div>
                            </Panel>
                        </template>
                    </div>
                </template>
            </DataView>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <InputText v-model="filter.search" placeholder="Search Courses" class="font-normal" />
                </div>
                <div class="">
                    <Button @click="goToEditProgram(programId as string)" size="small" label="Change Courses"></Button>
                </div>
            </div>
            <!-- <TabView class="horizontal tabview">
                <TabPanel v-for="(item, index) in programSubjects" :key="index">
                    <template #header>
                        <span>{{ item.title }}</span>
                    </template>
                    <div v-if="loading.list">
                        <div v-for="i in [1, 2, 3, 4, 5, 6]" :key="i" class="col-12">
                            <LoadingIndicator></LoadingIndicator>
                        </div>
                    </div>
                    <div v-else>
                        <div class="mb-8">
                            <div class="text-lg gap-2 flex items-center flex-wrap">
                                <span>{{ item.title }}</span>
                                <Tag :value="`${item.homework_count} Assessments`" severity="warning" />
                                 <Tag :value="`${item.lesson_count} Lessons`" severity="success" />
                            </div>
                            <Tag :value="`Faculty: ${item.author}`" severity="info" />
                            <a :href="getEditSubjectUrl(item.ID!)"><Button link class="!py-0"
                                    label="Edit Course"></Button></a>
                        </div>
                        <ProgramSubjectHomeworkSchedule :key="index" :homeworks="item.homeworks!" :subjectId="item.ID!"
                            :programId="(programId as string)" :dripMethod="currentProgram?.drip_method">
                        </ProgramSubjectHomeworkSchedule>
                    </div>
                </TabPanel>
            </TabView> -->
        </div>
    </template>
</template>

<style>
.input-number,
.input-number input {
    @apply w-16 !important;
}

@media md {
    .horizontal.tabview {
        @apply grid grid-cols-12;
    }

    .horizontal.tabview .navContainer {
        @apply col-span-3;
    }


    .horizontal.tabview .navContent>ul {
        @apply flex-col;
    }

    .horizontal.tabview .panelcontainer {
        @apply col-span-9;
    }
}
</style>
