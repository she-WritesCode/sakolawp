<script setup lang="ts">
import Toast from 'primevue/toast';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';
import { ref, onMounted, computed, watch } from 'vue';
import { DateHelper } from '../utils/date';
import { useHomeworkStore } from '../stores/homework';
import { useHomeworkDeliveryStore } from '../stores/homework-deliveries';
import { useProgramScheduleStore } from '../stores/program-schedule';
import { usePeerReviewStore } from '../stores/peer-review';
import { useProgramEnrollmentStore } from '../stores/program-enrollment';
import DynamicForm from '../components/homeworks/DynamicForm.vue';
import LoadingIndicator from '../components/LoadingIndicator.vue';


const homework_code = ref(new URL(window.location.href).searchParams.get('homework_code'));

const { filter: homeworkFilter, homeworks, loading: homeworkLoading } = useHomeworkStore()
const { filter: deliveryFilter, deliveries, createHomeworkDelivery, loading: deliveryLoading } = useHomeworkDeliveryStore()
const { filter: scheduleFilter, programSchedules, loading: scheduleLoading } = useProgramScheduleStore()
const { filter: peerReviewFilter, peerReviews, loading: peerReviewLoading } = usePeerReviewStore()
const { programEnrollments, fetchCurrentUserEnrollments } = useProgramEnrollmentStore()

const currentHomework = computed(() => {
    if (homeworks.value.length) {
        return homeworks.value[0]
    }
    return null;
})
const currentSubmission = computed(() => {
    if (deliveries.value.length) {
        return deliveries.value[0]
    }
    return null;
})
const currentSchedule = computed(() => {
    if (programSchedules.value.length) {
        return programSchedules.value[0]
    }
    return null;
})
const currentEnrollment = computed(() => {
    if (programEnrollments.value.length) {
        return programEnrollments.value[0]
    }
    return null;
})
onMounted(() => {
    fetchCurrentUserEnrollments()
    homeworkFilter.homework_code = homework_code.value!
    deliveryFilter.homework_code = homework_code.value!
})
watch(currentHomework, () => {
    scheduleFilter.content_id = currentHomework.value!.homework_id!
    scheduleFilter.content_type = "homework"
    scheduleFilter.class_id = currentEnrollment.value?.class_id ?? ""
})
watch(currentSubmission, () => {
    if (currentHomework.value && currentHomework.value.allow_peer_review) {
        peerReviewFilter.delivery_id = currentSubmission.value!.delivery_id!
        peerReviewFilter.class_id = currentEnrollment.value?.class_id ?? ""
        peerReviewFilter.peer_id = currentEnrollment.value?.student_id ?? ""
    }
})
watch(currentEnrollment, () => {
    scheduleFilter.content_id = currentHomework.value!.homework_id!
    scheduleFilter.content_type = "homework"
    scheduleFilter.class_id = currentEnrollment.value?.class_id ?? ""
})

const submitHomework = (values: Record<string, any>) => {
    createHomeworkDelivery({
        homework_code: currentHomework.value?.homework_code!,
        class_id: currentEnrollment.value?.class_id!,
        responses: values,
        homework_reply: '',
        student_comment: '',
        student_id: currentEnrollment.value?.student_id!,
    })
    console.log('Yay!', values)
}

const submitLabel = computed(() => currentSubmission.value ? 'Update Assessment Submission' : 'Submit Assessment')
const disableSubmission = computed(() => {
    if (currentSchedule.value) {
        return currentSchedule.value.actual_deadline_date_is_past!
    }
    return true
})

const peerReviewStarted = computed(() => !!peerReviews.value.length)
</script>
<template>
    <Toast position="bottom-center" />
    <div v-if="homeworkLoading.list || scheduleLoading.list || deliveryLoading.list || peerReviewLoading.list">
        <LoadingIndicator></LoadingIndicator>
    </div>
    <div v-else-if="currentHomework">
        <template v-if="!currentSchedule">
            <p class="text-xl">Assessment Schedule Not Found.</p>
        </template>
        <template v-else>
            <h3>{{ currentHomework.title ?? "" }}</h3>
            <div class="font-medium">Due Date: {{ DateHelper.fullDate(currentSchedule.actual_deadline_date!) }}</div>
            <div class="mb-2 flex gap-2 flex-wrap">
                <Tag v-if="currentHomework.allow_peer_review && currentHomework.peer_review_who == 'student'"
                    severity="warning" value="Peer Reviewed">
                </Tag>
                <Tag v-if="disableSubmission" severity="danger"
                    :value="`Sorry you cannot submit this assessment any longer. The deadline was ${DateHelper.relativeTime(currentSchedule.actual_deadline_date!)}`">
                </Tag>
                <Tag v-if="currentSubmission && !disableSubmission" severity="info"
                    :value="`You submitted this assessment on ${DateHelper.fullDate(currentSubmission.created_at!)}. \nHowever, you can still edit your response before the due date`">
                </Tag>
                <Tag v-if="currentSubmission && peerReviewStarted" severity="warning"
                    :value="`You submitted this assessment on ${DateHelper.fullDate(currentSubmission.created_at!)}. \nYour submission has been reviewed so you are not allowed to update it any more`">
                </Tag>
            </div>
            <div class="whitespace-break-spaces mb-4">{{ currentHomework.description ?? "" }}</div>
            <DynamicForm :demo="disableSubmission || peerReviewStarted" :submit="submitHomework"
                :initialResponse="currentSubmission?.responses" :questions="currentHomework!.questions"
                :submitLabel="submitLabel"></DynamicForm>
        </template>
    </div>
    <div v-else>
        <p class="text-xl">Assessment Not Found.</p>
    </div>
    <Divider />
</template>
<style scoped></style>