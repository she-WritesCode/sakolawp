<script setup lang="ts">
import { onMounted } from 'vue'
import { type Lesson } from '../../stores/lesson'
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
  lessons: Lesson[]
  subjectId: number
  programId: string
  dripMethod?: DripMethod
}>()
const { getOneProgram: getOneProgram, currentProgram } = useProgramStore()
const { errors, defineField, handleSubmit, setValues, values } = useForm<{
  [key: string]: ProgramSchedule
}>({
  initialValues: (props.lessons ?? []).reduce<{ [key: string]: ProgramSchedule }>((acc, lesson) => {
    acc[lesson.ID as string] = {
      subject_id: `${props.subjectId}`,
      class_id: props.programId as string,
      drip_method: (currentProgram.value?.drip_method || props.dripMethod) as DripMethod,
      content_id: lesson.ID as string,
      content_type: 'lesson',
      release_date: '',
      deadline_date: '',
      release_days: 0,
      deadline_days: 0,
      release_days_time: '',
      deadline_days_time: ''
    }
    return acc
  }, {}),
  validationSchema: toTypedSchema(createProgramScheduleSchema)
})

const schedules = reactive(
  Object.keys(values).reduce<{ [key: string]: ProgramSchedule }>((acc, ID) => {
    const [value] = defineField(ID)
    acc[ID] = value.value
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
    props.lessons.forEach((lesson) => {
      const existingSchedule = value.find(
        (sch) => sch.content_id == lesson.ID && sch.content_type == 'lesson'
      )
      if (existingSchedule) {
        schedules[lesson.ID as string] = {
          subject_id: `${props.subjectId}`,
          class_id: props.programId as string,
          drip_method: (currentProgram.value?.drip_method || props.dripMethod) as DripMethod,
          content_id: lesson.ID as string,
          content_type: 'lesson',
          release_date: existingSchedule.release_date,
          deadline_date: existingSchedule.deadline_date,
          release_days: existingSchedule.release_days,
          deadline_days: existingSchedule.deadline_days,
          release_days_time: existingSchedule.release_days_time,
          deadline_days_time: existingSchedule.deadline_days_time,
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
  Object.keys(values).reduce<{ [key: string]: string }>((acc, ID) => {

    acc[ID] = DateHelper.formatDate(
      DateHelper.addDays(
        currentProgram.value?.start_date ?? '',
        schedules[ID!]!.release_days
      ),
      'EEEE MMMM dd, yyyy'
    )
    return acc
  }, {})
)
const deadlineDate = computed(() =>
  Object.keys(values).reduce<{ [key: string]: string }>((acc, ID) => {
    acc[ID] = DateHelper.formatDate(
      DateHelper.addDays(releaseDate.value[ID], schedules[ID!]!.deadline_days),
      'EEEE MMMM dd, yyyy'
    )
    return acc
  }, {})
)

// Specific dates
const setDefaultReleaseTime = (ID: string) => {
  if (
    !schedules[ID!]!.release_date ||
    schedules[ID!]!.release_date == '0000-00-00 00:00:00'
  ) {
    schedules[ID!]!.release_date = DateHelper.toSimpleBackendDateAndTimeString(
      DateHelper.setTime(new Date(), 9)
    )
  }
}
const setDefaultDeadlineTime = (ID: string) => {
  if (
    !schedules[ID!]!.deadline_date ||
    schedules[ID!]!.deadline_date == '0000-00-00 00:00:00'
  ) {
    schedules[ID!]!.deadline_date = DateHelper.toSimpleBackendDateAndTimeString(
      DateHelper.setTime(new Date(), 23, 59)
    )
  }
}
// Deadline Days
const setDefaultReleaseDaysTime = (ID: string) => {
  if (
    !schedules[ID!]!.release_days_time ||
    schedules[ID!]!.release_days_time == '00:00'
  ) {
    schedules[ID!]!.release_days_time = DateHelper.toSimpleBackendTimeString(
      DateHelper.setTime(new Date(), 9)
    )
  }
}
const setDefaultDeadlineDaysTime = (ID: string) => {
  if (
    !schedules[ID!]!.deadline_days_time ||
    schedules[ID!]!.deadline_days_time == '00:00'
  ) {
    schedules[ID!]!.deadline_days_time = DateHelper.toSimpleBackendTimeString(
      DateHelper.setTime(new Date(), 23, 59)
    )
  }
}


const getEditLessonUrl = (ID: string) => {
  const url = new URL(window.location.href)
  url.pathname = '/wp-admin/post.php'
  url.search = ""
  url.searchParams.set('post', `${ID}`)
  url.searchParams.set('action', 'edit')
  // url.searchParams.set('expanded', '1')
  return url.toString()
}
const getAddLessonUrl = (subjectId: string | number) => {
  const url = new URL(window.location.href)
  url.pathname = '/wp-admin/post.php'
  url.search = ""
  url.searchParams.set('post', `${subjectId}`)
  url.searchParams.set('action', 'edit')
  url.searchParams.set('rig_action', 'add_lesson')
  url.searchParams.set('rig_tab', 'lessons')
  url.searchParams.set('expanded', '1')
  return url.toString()
}

</script>

<template>
  <form @submit="submitForm">
    <div v-if="lessons?.length" class="grid gap-0 mb-4">
      <div class="flex flex-col md:flex-row gap-4 justify-between md:items-center">
        <ul class="text-yellow-700 list-disc list-inside text-sm">
          <li>If no deadline is set the student will continue to have access to submit the lesson at
            any time.</li>

          <li>If the release date is not set the lesson would be released to the students by default</li>
        </ul>
        <Button type="submit" class="w-64" label="Save Schedule"></Button>
      </div>
      <Divider />
      <template v-for="lesson in lessons" :key="lesson.ID">
        <div class="flex flex-col md:flex-row gap-4 items-center md:justify-between">
          <div class="w-full">
            <div class="text-base">{{ lesson.title }}</div>
            <a :href="getEditLessonUrl(lesson.ID!)"><Button link class="!py-0" label="Edit Lesson"></Button></a>
          </div>
          <div class="w-full md:max-w-96 lg:max-w-[600px]">
            <div v-if="dripMethod == 'days_after_release'" class="flex flex-col gap-4">
              <div>
                <div class="flex gap-4">
                  <div>
                    <label>Release</label>
                    <div class="flex gap-2 items-center">
                      <InputNumber v-model.number="schedules[lesson.ID!]!.release_days" class="input-number"
                        placeholder="0" type="number" />
                      <span>days after program starts</span>
                    </div>
                  </div>
                  <div>
                    <label>Time</label>
                    <div class="flex gap-2 items-center">
                      <input v-model="schedules[lesson.ID!]!.release_days_time" class="input-time w-full"
                        placeholder="0" type="time" @focus="setDefaultReleaseDaysTime(lesson.ID!)" />
                    </div>
                  </div>
                </div>
                <div>
                  <Tag :value="releaseDate[lesson.ID!]" severity="secondary" />
                </div>
              </div>
              <div>
                <div class="flex gap-4">
                  <div>
                    <label>Deadline</label>
                    <div class="flex gap-2 items-center">
                      <InputNumber v-model.number="schedules[lesson.ID!]!.deadline_days" class="input-number"
                        placeholder="0" type="number" />
                      <span>days after release date</span>
                    </div>
                  </div>
                  <div>
                    <label>Time</label>
                    <div class="flex gap-2 items-center">
                      <input v-model="schedules[lesson.ID!]!.deadline_days_time" class="input-time w-full"
                        placeholder="0" type="time" @focus="setDefaultDeadlineDaysTime(lesson.ID!)" />
                    </div>
                  </div>
                </div>
                <div>
                  <Tag :value="deadlineDate[lesson.ID!]" severity="secondary" />
                </div>
              </div>
            </div>
            <div v-else class="flex flex-col gap-2">
              <div>
                <label>Release</label>
                <input class="w-full" type="datetime-local" v-model="schedules[lesson.ID!]!.release_date"
                  @focus="setDefaultReleaseTime(lesson.ID!)" />
              </div>
              <div>
                <label>Deadline</label>
                <input class="w-full" type="datetime-local" v-model="schedules[lesson.ID!]!.deadline_date"
                  @focus="setDefaultDeadlineTime(lesson.ID!)" :min="schedules[lesson.ID!]!.release_date" />
              </div>
            </div>
          </div>
        </div>
        <Divider />
      </template>
      <div class="flex flex-col md:flex-row gap-4 justify-between md:items-center">
        <ul class="text-yellow-700 list-disc list-inside text-sm">
          <li>If no deadline is set the student will continue to have access to submit the lesson at
            any time.</li>

          <li>If the release date is not set the lesson would be released to the students by default</li>
        </ul>
        <Button type="submit" class="w-64" label="Save Schedule"></Button>
      </div>
    </div>
    <div v-else class="w-full text-center py-16">
      <p class="text-xl mb-2">No lessons yet</p>
      <p class="text-base mb-4">Add lessons for this courses first. Then you can schedule it here in the program.
      </p>
      <a :href="getAddLessonUrl(subjectId)"><Button icon="pi pi-external-link" label="Add Lesson"></Button></a>
    </div>
  </form>
</template>

<style>
.input-number,
.input-number input {
  @apply w-16 !important;
}
</style>
