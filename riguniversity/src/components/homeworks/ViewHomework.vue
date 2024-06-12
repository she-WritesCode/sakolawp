<script setup lang="ts">
import Tag from 'primevue/tag';
import { useHomeworkStore } from '../../stores/homework';
import { useHomeworkDeliveryStore } from '../../stores/homework-deliveries';
import Divider from 'primevue/divider';
import DataTable from 'primevue/datatable';
import Button from 'primevue/button';
import { onMounted } from 'vue';
import Column from 'primevue/column';


const { currentHomework, homeworkId, getOneHomework, goToEditHomework } = useHomeworkStore()
const { deliveries, filter, goToViewHomeworkDelivery } = useHomeworkDeliveryStore()

onMounted(() => {
    if (homeworkId) {
        getOneHomework(homeworkId)
        filter.homework_code = currentHomework.value?.homework_code as string
    }
})

</script>
<template>
    <div v-if="!homeworkId && !currentHomework">
        <h1 class="text-3xl mb-4">Homework Not Found</h1>
        <p class="mb-4">Please go back and choose a different homework</p>
        <Button label="Back to Homework List"></Button>
    </div>
    <template v-else>
        <div class="border border-primary-50 p-4 rounded-lg w-full mb-8">
            <div class="w-full flex gap-2 mb-4">
                <h3 class="text-2xl font-semibold text-primary-900">{{ currentHomework?.title }}</h3>
                <Button size="small" @click="goToEditHomework(homeworkId as string)" class="!text-xs" outlined
                    label="Edit Homework"></Button>
            </div>

            <div class="mb-4">{{ currentHomework?.description }}</div>

            <div class="flex flex-wrap gap-2 capitalize mb-4">
                <div class="">
                    <Tag outlined :value="`${currentHomework?.questions?.length || '0'} Questions`" severity="info" />
                </div>
                <template v-if="currentHomework?.allow_peer_review">
                    <div class="">
                        <Tag outlined :value="`${currentHomework?.peer_review_who} Reviewed`" severity="success" />
                    </div>
                    <div class="">
                        <Tag outlined :value="currentHomework?.peer_review_template || 'No Rubric'"
                            severity="warning" />
                    </div>
                </template>
                <div v-else class="">
                    <Tag outlined value="Faculty Graded" severity="success" />
                </div>
                <div class="">
                    <Tag outlined
                        :value="`Due on: ${new Date(currentHomework?.date_end as any).toDateString()} ${currentHomework?.time_end}`"
                        severity="danger" />
                </div>
            </div>

            <div class="mb-4">
                <h4 class="mb-2 text-lg">Questions</h4>
                <ol class="!list-decima">
                    <li class="flex gap-2 items-center" v-for="(question, index) in currentHomework?.questions"
                        :key="question.question_id">
                        <span class="">{{ index + 1 }}. </span>
                        <span>{{ question.question }}</span>
                        <Tag :value="question.type" severity="secondary" />
                    </li>
                </ol>
            </div>
        </div>

        <div>
            <div class="mb-4">
                <h3 class="text-lg mb-0">Submissions ({{ currentHomework?.delivery_count }})</h3>
                <!-- <Divider /> -->
            </div>

            <div>
                <DataTable :value="deliveries">
                    <Column field="student_name" header="Student"></Column>
                    <Column field="created_at" header="Submitted on"></Column>
                    <Column field="mark" header="Mark">
                        <template #body="slotProps">
                            <Tag v-if="slotProps.data.mark" :value="slotProps.data.mark" severity="secondary" />
                            <i class="text-sm" v-else>Not marked</i>
                        </template>
                    </Column>
                    <Column header="">
                        <template #body="slotProps">
                            <div class="flex gap-2 text-sm">
                                <Button outlined size="small"
                                    @click="goToViewHomeworkDelivery(slotProps.data.subject_id)" label="View"></Button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </template>
</template>

<style></style>