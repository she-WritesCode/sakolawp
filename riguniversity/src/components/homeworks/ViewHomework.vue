<script setup lang="ts">
import Tag from 'primevue/tag';
import { useHomeworkStore } from '../../stores/homework';
import Divider from 'primevue/divider';
import DataTable from 'primevue/datatable';
import Button from 'primevue/button';
import { onMounted } from 'vue';


const { currentHomework, homeworkId, getOneHomework, goToEditHomework } = useHomeworkStore()

onMounted(() => {
    if (homeworkId) {
        getOneHomework(homeworkId)
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

            <div class="mb-4"> {{ currentHomework?.description }}</div>

            <div class="flex flex-wrap gap-2 capitalize">
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
        </div>

        <div>
            <div class="mb-4">
                <h3 class="text-lg mb-0">Home Work Responses</h3>
                <Divider />
            </div>

            <div>
                <DataTable></DataTable>
            </div>
        </div>
    </template>
</template>

<style></style>