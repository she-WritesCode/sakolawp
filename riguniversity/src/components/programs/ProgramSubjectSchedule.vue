<script setup lang="ts">
import { onMounted } from 'vue'
import { type Homework } from '../../stores/homework'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import InputNumber from 'primevue/inputnumber'
import { useForm } from 'vee-validate'
import {
  useProgramScheduleStore,
  createProgramScheduleSchema,
  type ProgramSchedule
} from '../../stores/program-schedule'
import { type DripMethod, useProgramStore } from '../../stores/program'
import { DateHelper } from '../../utils/date'
import { toTypedSchema } from '@vee-validate/yup'
import { watch } from 'vue'
import { reactive } from 'vue'
import { computed } from 'vue'

const props = defineProps<{
  homeworks: Homework[]
  subjectId: number
  programId: string
  dripMethod?: DripMethod
}>()
const { getOneProgram: getOneProgram, currentProgram } = useProgramStore()
const { errors, defineField, handleSubmit, setValues, values } = useForm<{
  [key: string]: ProgramSchedule
}>({
  initialValues: (props.homeworks??[]).reduce<{ [key: string]: ProgramSchedule }>((acc, homework) => {
    acc[homework.homework_id as string] = {
      subject_id: `${props.subjectId}`,
      class_id: props.programId as string,
      drip_method: (currentProgram.value?.drip_method || props.dripMethod) as DripMethod,
      content_id: homework.homework_id as string,
      content_type: 'homework',
      release_date: '',
      deadline_date: '',
      release_days: 0,
      deadline_days: 0
    }
    return acc
  }, {}),
  validationSchema: toTypedSchema(createProgramScheduleSchema)
})

const schedules = reactive(
  Object.keys(values).reduce<{ [key: string]: ProgramSchedule }>((acc, homework_id) => {
    const [value] = defineField(homework_id)
    acc[homework_id] = value.value
    return acc
  }, {})
)

const { createProgramSchedule, filter, programSchedules, fetchProgramSchedules } =
  useProgramScheduleStore(`${props.subjectId}-ProgramSchedule`)

const submitForm = handleSubmit((values) => {
  console.log(values)
  createProgramSchedule(Object.values(values))
})

watch(schedules, (value) => {
  if (value) setValues({ ...values, ...value })
})
watch(programSchedules, (value) => {
  if (value && value.length) {
    props.homeworks.forEach((homework) => {
      const existingSchedule = value.find(
        (sch) => sch.content_id == homework.homework_id && sch.content_type == 'homework'
      )
      if (existingSchedule) {
        schedules[homework.homework_id as string] = {
          subject_id: `${props.subjectId}`,
          class_id: props.programId as string,
          drip_method: (currentProgram.value?.drip_method || props.dripMethod) as DripMethod,
          content_id: homework.homework_id as string,
          content_type: 'homework',
          release_date: existingSchedule.release_date,
          deadline_date: existingSchedule.deadline_date,
          release_days: existingSchedule.release_days,
          deadline_days: existingSchedule.deadline_days
        }
      }
    })
  }
})

onMounted(() => {
  filter.subject_id = `${props.subjectId}`
  filter.class_id = props.programId as string
  fetchProgramSchedules()
  if (props.programId && !currentProgram.value) getOneProgram(props.programId)
})

const releaseDate = computed(() =>
  Object.keys(values).reduce<{ [key: string]: string }>((acc, homework_id) => {
  
    acc[homework_id] = DateHelper.formatDate(
      DateHelper.addDays(
        currentProgram.value?.start_date ?? '',
        schedules[homework_id!]!.release_days
      )
    )
    return acc
  }, {})
)
const deadlineDate = computed(() =>
  Object.keys(values).reduce<{ [key: string]: string }>((acc, homework_id) => {
    acc[homework_id] = DateHelper.formatDate(
      DateHelper.addDays(releaseDate.value[homework_id], schedules[homework_id!]!.deadline_days)
    )
    return acc
  }, {})
)

const setDefaultReleaseTime = (homework_id: string) => {
  if (
    !schedules[homework_id!]!.release_date ||
    schedules[homework_id!]!.release_date == '0000-00-00 00:00:00'
  ) {
    schedules[homework_id!]!.release_date = DateHelper.toSimpleBackendDateAndTimeString(
      DateHelper.setTime(new Date(), 9)
    )
  }
}
const setDefaultDeadlineTime = (homework_id: string) => {
  if (
    !schedules[homework_id!]!.deadline_date ||
    schedules[homework_id!]!.deadline_date == '0000-00-00 00:00:00'
  ) {
    schedules[homework_id!]!.deadline_date = DateHelper.toSimpleBackendDateAndTimeString(
      DateHelper.setTime(new Date(), 23, 59)
    )
  }
}
</script>

<template>
  <form @submit="submitForm">
    <div>program starts on: {{ currentProgram?.start_date || 'error' }}</div>
    <Divider />
    <div class="grid gap-0 mb-4">
      <template v-for="homework in homeworks" :key="homework.homework_id">
        <div class="flex flex-col md:flex-row gap-4 items-center md:justify-between">
          <div class="text-base w-full">
            <div>{{ homework.title }}</div>
            {{ schedules[homework.homework_id!]!.release_date }}
            <Tag :value="`${homework.questions.length} Questions`" severity="secondary" />
          </div>
          <div class="w-full md:max-w-96 lg:max-w-[500px]">
            <div v-if="dripMethod == 'days_after_release'" class="flex flex-col md:flex-row gap-4">
              <div>
                <label>Release</label>
                <div class="flex gap-2 items-center">
                  <InputNumber
                    v-model.number="schedules[homework.homework_id!]!.release_days"
                    class="input-number"
                    placeholder="0"
                    type="number"
                  />
                  <span>days after program starts</span>
                </div>
                <div>
                  <Tag :value="releaseDate[homework.homework_id!]" severity="secondary" />
                </div>
              </div>
              <div>
                <label>Deadline</label>
                <div class="flex gap-2 items-center">
                  <InputNumber
                    v-model.number="schedules[homework.homework_id!]!.deadline_days"
                    class="input-number"
                    placeholder="0"
                    type="number"
                  />
                  <span>days after release date</span>
                </div>
                <div>
                  <Tag :value="deadlineDate[homework.homework_id!]" severity="secondary" />
                </div>
              </div>
            </div>
            <div v-else class="flex flex-col gap-2">
              <div>
                <label>Release</label>
                <input
                  class="w-full"
                  type="datetime-local"
                  v-model="schedules[homework.homework_id!]!.release_date"
                  @focus="setDefaultReleaseTime(homework.homework_id!)"
                />
              </div>
              <div>
                <label>Deadline</label>
                <input
                  class="w-full"
                  type="datetime-local"
                  v-model="schedules[homework.homework_id!]!.deadline_date"
                  @focus="setDefaultDeadlineTime(homework.homework_id!)"
                  :min="schedules[homework.homework_id!]!.release_date"
                />
              </div>
            </div>
          </div>
        </div>
        <Divider />
      </template>
      <div class="flex flex-col md:flex-row gap-4 justify-between md:items-center">
        <div class="text-yellow-700">
          If no deadline is set the student will continue to have access to submit the homework at
          any time
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
