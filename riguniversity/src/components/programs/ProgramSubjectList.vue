<script setup lang="ts">
import { onMounted } from "vue";
import { useprogramSubjectStore } from "../../stores/program-subject";
import { useProgramStore } from "../../stores/program";
import DataView from 'primevue/dataview';
import Panel from 'primevue/panel';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import LoadingIndicator from '../LoadingIndicator.vue'
import programSubjectSchedule from './programSubjectSchedule.vue'


const {
    programSubjects,
    filter,
    programSubjectId,
    loading,
} = useprogramSubjectStore();
const {
    programId, goToEditProgram: goToEditprogram, currentProgram: currentprogram
} = useProgramStore();

onMounted(() => {
    if (!programSubjectId) {
        filter.class_id = programId || ''
    }
});

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
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Subjects</h3>
                <p>Click on + to manage the schedule of every course</p>
            </div>

            <DataView dataKey="class_id" :value="programSubjects" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="filter.search" placeholder="Search Subjects" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToEditprogram(programId as string)" size="small"
                                label="Change Subjects"></Button>
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
                                            <span>{{ item.name }}</span>
                                            <Tag :value="`${item.homework_count} Homeworks`" severity="warning" />
                                            <!-- <Tag :value="`${item.lesson_count} Lessons`" severity="success" /> -->
                                        </div>
                                        <Tag :value="`Faculty: ${item.teacher_name}`" severity="info" />
                                    </div>
                                </template>

                                <!-- <template #togglericon="collapsed">
                                    <div v-if="collapsed"> <i icon="pi pi-cog"></i></div>
                                    <div v-if="!collapsed"> <i icon="pi pi-close"></i></div>
                                </template> -->

                                <programSubjectSchedule :key="index" :homeworks="item.homeworks"
                                    :subjectId="item.subject_id" :programId="programId as string"
                                    :dripMethod="currentprogram?.drip_method"></programSubjectSchedule>
                            </Panel>
                        </template>
                    </div>
                </template>
            </DataView>
        </div>
    </template>
</template>

<style>
.input-number,
.input-number input {
    @apply w-16 !important;
}
</style>
