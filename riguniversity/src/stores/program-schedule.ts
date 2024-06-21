import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'
import type { Homework } from './homework'
import * as yup from 'yup'
import type { DripMethod } from './program'

export interface programSchedule {
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

export const createprogramScheduleSchema = yup.object<programSchedule>({
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

export const useprogramScheduleStore = defineStore('programSchedule', () => {
  const toast = useToast()
  const programSchedules = ref<programSchedule[]>([])
  const currentprogramSchedule = ref<programSchedule | undefined>(undefined)
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
  const programScheduleId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('schedule_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_programSchedule')
  const showViewScreen = computed(
    () => action.value === 'view_programSchedule' && !!programScheduleId.value
  )
  const showEditScreen = computed(
    () => action.value === 'add_programSchedule' && !!programScheduleId.value
  )

  watch(filter, () => {
    fetchprogramSchedules()
  })

  const fetchprogramSchedules = () => {
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
        programSchedules.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewprogramSchedule = (programScheduleId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_programSchedule')
    url.searchParams.set('schedule_id', programScheduleId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewprogramSchedule = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('schedule_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditprogramSchedule = (programScheduleId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programSchedule')
    url.searchParams.set('schedule_id', programScheduleId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditprogramSchedule = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('schedule_id', programScheduleId.value as string)
    url.searchParams.set('action', 'view_programSchedule')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programSchedule')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneprogramSchedule = (id: string) => {
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
        currentprogramSchedule.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createprogramSchedule = (args: Partial<programSchedule>[]) => {
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
        currentprogramSchedule.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'program Schedule created successfully',
          life: 3000
        })
        // closeAddForm()
        // fetchprogramSchedules()
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        loading.create = false
      })
  }

  const updateprogramSchedule = (args: Partial<programSchedule>) => {
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
        getOneprogramSchedule(programScheduleId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'program Schedule updated successfully',
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

  const deleteprogramSchedule = (id: string) => {
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
        fetchprogramSchedules()
      })
  }

  return {
    programSchedules: computed(() => programSchedules),
    fetchprogramSchedules,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentprogramSchedule: computed(() => currentprogramSchedule),
    goToViewprogramSchedule,
    closeViewprogramSchedule,
    programScheduleId,
    getOneprogramSchedule,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createprogramSchedule,
    updateprogramSchedule,
    deleteprogramSchedule,
    goToEditprogramSchedule,
    closeEditprogramSchedule
  }
})
