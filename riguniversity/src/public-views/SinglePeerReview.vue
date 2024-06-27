<script setup lang="ts">
import Toast from 'primevue/toast';
import Tag from 'primevue/tag';
import { ref, onMounted, computed, watch } from 'vue';
import { DateHelper } from '../utils/date';
import { useHomeworkStore } from '../stores/homework';
import { useHomeworkDeliveryStore } from '../stores/homework-deliveries';
import { usePeerReviewStore } from '../stores/peer-review';
import { useProgramEnrollmentStore } from '../stores/program-enrollment';
import DynamicForm from '../components/homeworks/DynamicForm.vue';
import LoadingIndicator from '../components/LoadingIndicator.vue';
import HomeworkContent from "../components/homeworks/HomeworkContent.vue";


const delivery_id = ref(new URL(window.location.href).searchParams.get('delivery_id'));

const { filter: homeworkFilter, homeworks, loading: homeworkLoading, homeworkId } = useHomeworkStore()
const { currentDelivery, deliveryId, getOneHomeworkDelivery, loading: deliveryLoading } = useHomeworkDeliveryStore()
const { filter: peerReviewFilter, peerReviews, loading: peerReviewLoading } = usePeerReviewStore()
const { programEnrollments, fetchCurrentUserEnrollments } = useProgramEnrollmentStore()

const currentHomework = computed(() => {
    if (homeworks.value.length) {
        return homeworks.value[0]
    }
    return null;
})
onMounted(() => {
    getOneHomeworkDelivery(deliveryId!)
})
watch(currentDelivery, () => {
    homeworkFilter.homework_code = currentDelivery.value?.homework_code!
})

const questionMap = computed<Record<string, any>>(() => (currentHomework.value?.questions || []).reduce((acc, curr) => {
    acc[curr.question_id] = curr.question
    return acc;
}, {} as Record<string, any>))

/*
FOR NOW THIS ONLY DISPLAYS HOMEWORK CONTENT
WILL COMPLETE IT LATER
*/
</script>
<template>
    <Toast position="bottom-center" />
    <div v-if="currentDelivery">
        <div v-if="!currentDelivery.responses">
            <HomeworkContent :content="currentDelivery.homework_reply"></HomeworkContent>
        </div>
        <div v-else v-for="(key) in Object.keys(currentDelivery.responses || [])" :key="key">
            <div class="font-medium text-lg">{{ questionMap[key] }}</div>
            <HomeworkContent :content="currentDelivery.responses[key]"></HomeworkContent>
        </div>
    </div>
</template>