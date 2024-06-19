import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'
import type { Homework } from './homework'
import * as yup from 'yup'
import type { DripMethod } from './cohort'

export interface CohortSchedule {
  id?: string
  subject_id: string
  class_id: string
  content_id: string
  content_type: string
  drip_method: DripMethod
  release_date: string
  deadline_date: string
  release_days: number
  deadline_days: number
  created_at?: string
  updated_at?: string
}

export const createCohortScheduleSchema = yup.object<CohortSchedule>({
  schedules: yup
    .array()
    .of(
      yup.object({
        subject_id: yup.string().required(),
        class_id: yup.string().required(),
        content_id: yup.string().required(),
        content_type: yup.string().required(),
        drip_method: yup.string().required(),
        release_date: yup.string().required(),
        deadline_date: yup.string().required(),
        release_days: yup.string().required(),
        deadline_days: yup.string().required()
      })
    )
    .min(1)
})

export const useCohortScheduleStore = defineStore('cohortSchedule', () => {
  const toast = useToast()
  const cohortSchedules = ref<CohortSchedule[]>([])
  const currentCohortSchedule = ref<CohortSchedule | undefined>(undefined)
  const filter = reactive({
    search: '',
    subject_id: '',
    class_id: ''
  })
  const loading = reactive({
    list: false,
    get: false,
    create: false,
    update: false,
    delete: false
  })
  const cohortScheduleId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('schedule_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_cohortSchedule')
  const showViewScreen = computed(
    () => action.value === 'view_cohortSchedule' && !!cohortScheduleId.value
  )
  const showEditScreen = computed(
    () => action.value === 'add_cohortSchedule' && !!cohortScheduleId.value
  )

  watch(filter, () => {
    fetchCohortSchedules()
  })

  const fetchCohortSchedules = () => {
    loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_schedules',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        cohortSchedules.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewCohortSchedule = (cohortScheduleId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_cohortSchedule')
    url.searchParams.set('schedule_id', cohortScheduleId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewCohortSchedule = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('schedule_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditCohortSchedule = (cohortScheduleId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortSchedule')
    url.searchParams.set('schedule_id', cohortScheduleId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditCohortSchedule = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('schedule_id', cohortScheduleId.value as string)
    url.searchParams.set('action', 'view_cohortSchedule')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortSchedule')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneCohortSchedule = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_schedule',
        schedule_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohortSchedule.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createCohortSchedule = (args: Partial<CohortSchedule>[]) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_create_schedule',
        schedules: args
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohortSchedule.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Cohort Schedule created successfully',
          life: 3000
        })
        // closeAddForm()
        // fetchCohortSchedules()
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        loading.create = false
      })
  }

  const updateCohortSchedule = (args: Partial<CohortSchedule>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_schedule',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneCohortSchedule(cohortScheduleId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Cohort Schedule updated successfully',
          life: 3000
        })
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.update = false
      })
  }

  const deleteCohortSchedule = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_schedule',
        schedule_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchCohortSchedules()
      })
  }

  return {
    cohortSchedules: computed(() => cohortSchedules),
    fetchCohortSchedules,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentCohortSchedule: computed(() => currentCohortSchedule),
    goToViewCohortSchedule,
    closeViewCohortSchedule,
    cohortScheduleId,
    getOneCohortSchedule,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createCohortSchedule,
    updateCohortSchedule,
    deleteCohortSchedule,
    goToEditCohortSchedule,
    closeEditCohortSchedule
  }
})
