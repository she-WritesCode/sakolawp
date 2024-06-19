<script setup lang="ts">
import { onMounted } from "vue";
import { type Homework } from "../../stores/homework";
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Divider from 'primevue/divider';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import { useForm } from 'vee-validate';
import { useCohortScheduleStore, createCohortScheduleSchema, type CohortSchedule } from '../../stores/cohort-schedule';
import { type DripMethod, useCohortStore } from '../../stores/cohort';
import { DateHelper } from '../../utils/date';
import { toTypedSchema } from '@vee-validate/yup';
import { watch } from "vue";
import { reactive } from "vue";
import { computed } from "vue";

const props = defineProps<{
    homeworks: Homework[];
    subjectId: string
    cohortId: string
    dripMethod?: DripMethod
}>()
const { getOneCohort, currentCohort } = useCohortStore()
const { errors, defineField, handleSubmit, setValues, values } = useForm<{ [key: string]: CohortSchedule }>({
    initialValues: props.homeworks.reduce<{ [key: string]: CohortSchedule }>((acc, homework) => {
        acc[homework.homework_id as string] = {
            subject_id: props.subjectId as string,
            class_id: props.cohortId as string,
            drip_method: (currentCohort.value?.drip_method || props.dripMethod) as DripMethod,
            content_id: homework.homework_id as string,
            content_type: "homework",
            release_date: "",
            deadline_date: "",
            release_days: 0,
            deadline_days: 0,
        }
        return acc
    }, {}),
    validationSchema: toTypedSchema(createCohortScheduleSchema),
});

const schedules = reactive(Object.keys(values).reduce<{ [key: string]: CohortSchedule }>((acc, homework_id) => {
    const [value] = defineField(homework_id)
    acc[homework_id] = value.value
    return acc
}, {}));

const { createCohortSchedule, filter, cohortSchedules, fetchCohortSchedules } = useCohortScheduleStore()

const submitForm = handleSubmit((values) => {
    console.log(values);
    createCohortSchedule(Object.values(values))
});

watch(schedules, (value) => {
    if (value) setValues({ ...values, ...value })
})
watch(cohortSchedules, (value) => {
    if (value && value.length) {
        setValues({
            ...values,
            ...props.homeworks.reduce<{ [key: string]: CohortSchedule }>((acc, homework) => {
                const existingSchedule = value.find(sch => sch.content_id == homework.homework_id && sch.content_type == 'homework')
                if (existingSchedule) {
                    acc[homework.homework_id as string] = {
                        subject_id: props.subjectId as string,
                        class_id: props.cohortId as string,
                        drip_method: (currentCohort.value?.drip_method || props.dripMethod) as DripMethod,
                        content_id: homework.homework_id as string,
                        content_type: "homework",
                        release_date: existingSchedule.release_date,
                        deadline_date: existingSchedule.deadline_date,
                        release_days: existingSchedule.release_days,
                        deadline_days: existingSchedule.deadline_days,
                    }
                }
                return acc
            }, {})
        })
    }
})

onMounted(() => {
    filter.subject_id = props.subjectId
    filter.class_id = props.cohortId as string
    fetchCohortSchedules()
    if (props.cohortId && !currentCohort.value) getOneCohort(props.cohortId)
})


const releaseDate = computed(() => Object.keys(values).reduce<{ [key: string]: string }>((acc, homework_id) => {
    acc[homework_id] = DateHelper.formatDate(DateHelper.addDays(currentCohort.value!.start_date, schedules[homework_id!]!.release_days))
    return acc
}, {}));
const deadlineDate = computed(() => Object.keys(values).reduce<{ [key: string]: string }>((acc, homework_id) => {
    acc[homework_id] = DateHelper.formatDate(DateHelper.addDays(releaseDate.value[homework_id], schedules[homework_id!]!.deadline_days))
    return acc
}, {}));
</script>

<template>
    <form @submit="submitForm">
        <div> Cohort starts on: {{ currentCohort?.start_date || "error" }}</div>
        <Divider />
        <div class="grid gap-0 mb-4">
            <template v-for="homework in homeworks" :key="homework.homework_id">
                <div class="flex flex-col md:flex-row gap-4 items-center md:justify-between">
                    <div class="text-base w-full">
                        <div>{{ homework.title }}</div>
                        <Tag :value="`${homework.questions.length} Questions`" severity="secondary" />
                    </div>
                    <div class="w-full md:max-w-96 lg:max-w-[500px]">
                        <div v-if="dripMethod == 'days_after_release'" class="flex flex-col md:flex-row gap-4">
                            <div>
                                <label>Release</label>
                                <div class="flex gap-2 items-center">
                                    <InputNumber v-model.number="schedules[homework.homework_id!]!.release_days"
                                        class="input-number" placeholder="0" type="number" />
                                    <span>days after cohort starts</span>
                                </div>
                                <div>
                                    <Tag :value="releaseDate[homework.homework_id!]" severity="secondary" />
                                </div>
                            </div>
                            <div>
                                <label>Deadline</label>
                                <div class="flex gap-2 items-center">
                                    <InputNumber v-model.number="schedules[homework.homework_id!]!.deadline_days"
                                        class="input-number" placeholder="0" type="number" />
                                    <span>days after release date</span>
                                </div>
                                <div>
                                    <Tag :value="deadlineDate[homework.homework_id!]" severity="secondary" />
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col md:flex-row gap-4">
                            <div>
                                <label>Release</label>
                                <InputText class="w-full" type="date"
                                    v-model.number="schedules[homework.homework_id!]!.release_date" />
                                <!-- :defaultValue="releaseDate[homework.homework_id!]" -->
                            </div>
                            <div>
                                <label>Deadline</label>
                                <input class="w-full" type="date"
                                    v-model.number="schedules[homework.homework_id!]!.deadline_date"
                                    :defaultValue="deadlineDate[homework.homework_id!]" />
                            </div>
                        </div>
                    </div>
                </div>
                <Divider />
            </template>
            <div class="flex flex-col md:flex-row gap-4 justify-between md:items-center">
                <div class="text-yellow-700">
                    If no deadline is set the student will continue to have access to submit the
                    homework at any time
                </div>
                <Button type="submit" class="w-64" label="Save Schedule"></Button>
            </div>
        </div>
    </form>
</template>

<style>
.input-number,
.input-number input {
    @apply w-16 !important;
}
</style>
