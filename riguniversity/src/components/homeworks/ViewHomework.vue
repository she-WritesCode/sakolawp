<script setup lang="ts">
import Tag from 'primevue/tag';
import { useHomeworkStore } from '../../stores/homework';
import Divider from 'primevue/divider';
import DataTable from 'primevue/datatable';


const { loading, currentHomework } = useHomeworkStore()

</script>
<template>
    <div class="bg-primary-600 text-primary-inverse p-4 rounded-lg w-full mb-8">
        <div class="w-full mb-4">
            <h3 class="text-lg font-semibold text-primary-inverse">{{ currentHomework?.title }}</h3>
        </div>
        <div> {{ currentHomework?.description }}</div>
        <div class="flex flex-wrap gap-4 capitalize">
            <div>
                <Tag :value="currentHomework?.responses?.length || '0'" severity="secondary" />
                Questions
            </div>
            <template v-if="currentHomework?.allow_peer_review">
                <div>
                    <Tag :value="currentHomework?.peer_review_who" severity="secondary" />
                    Reviewed
                </div>
                <div>
                    <Tag :value="currentHomework?.peer_review_template || 'No'" severity="secondary" />
                    Rubric
                </div>
            </template>
            <div v-else>
                <Tag value="Faculty" severity="secondary" />
                Graded
            </div>
            <div>
                <Tag :value="currentHomework?.responses?.length || '0'" severity="secondary" />
                Questions
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

<style></style>