<script setup lang="ts">
import Toast from 'primevue/toast';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';
import { ref, onMounted, computed, watch } from 'vue';
import { DateHelper } from '../utils/date';
import { useHomeworkStore } from '../stores/homework';
import { useHomeworkDeliveryStore } from '../stores/homework-deliveries';
import { useProgramScheduleStore } from '../stores/program-schedule';
import { useProgramEnrollmentStore } from '../stores/program-enrollment';
import DynamicForm from '../components/homeworks/DynamicForm.vue';


const homework_code = ref(new URL(window.location.href).searchParams.get('homework_code'));

const { filter: homeworkFilter, homeworks } = useHomeworkStore()
const { filter: deliveryFilter, deliveries } = useHomeworkDeliveryStore()
const { filter: scheduleFilter, programSchedules } = useProgramScheduleStore()
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
watch(currentEnrollment, () => {
    scheduleFilter.content_id = currentHomework.value!.homework_id!
    scheduleFilter.content_type = "homework"
    scheduleFilter.class_id = currentEnrollment.value?.class_id ?? ""
})

const submitHomework = (values) => {
    // createDelivery
    console.log('Yay!', values)
}
</script>
<template>
    <Toast position="bottom-center" />
    <div v-if="currentHomework">
        <template v-if="!currentSchedule">
            <p class="text-xl">Assessment Schedule Not Found.</p>
        </template>
        <template v-else>
            <h2>{{ currentHomework.title ?? "" }}</h2>
            <div class="mb-2 flex gap-2 flex-wrap">
                <Tag severity="info"
                    :value="`Due on : ${DateHelper.formatDate(currentSchedule.actual_deadline_date)} ${DateHelper.formatTime(currentSchedule.actual_deadline_date)}`">
                </Tag>
                <Tag v-if="currentHomework.allow_peer_review && currentHomework.peer_review_who == 'student'"
                    severity="warning" value="Peer Reviewed">
                </Tag>
            </div>
            <div class="whitespace-break-spaces mb-4">{{ currentHomework.description ?? "" }}</div>
            <DynamicForm :submit="submitHomework" :initialResponse="currentSubmission?.responses"
                :questions="currentHomework!.questions" submitLabel="Submit Assessment"></DynamicForm>
        </template>
    </div>
    <div v-else>
        <p class="text-xl">Assessment Not Found.</p>
    </div>
    <Divider />
</template>
<style scoped></style>