import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'
import * as yup from 'yup'
import type { DripMethod } from './program'

export interface ProgramSchedule {
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
  release_days_time: string
  deadline_days_time: string
  created_at?: string
  updated_at?: string
}

export const createProgramScheduleSchema = yup.object<ProgramSchedule>({
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

export const useProgramScheduleStore = (uniqueStoreName = 'programSchedule') =>
  defineStore(uniqueStoreName, () => {
    const toast = useToast()
    const programSchedules = ref<ProgramSchedule[]>([])
    const currentProgramSchedule = ref<ProgramSchedule | undefined>(undefined)
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
      fetchProgramSchedules()
    })

    const fetchProgramSchedules = () => {
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

    const goToViewProgramSchedule = (programScheduleId: string) => {
      const url = new URL(window.location.href)
      url.searchParams.set('action', 'view_programSchedule')
      url.searchParams.set('schedule_id', programScheduleId)
      // showViewScreen.value = true
      window.location.href = url.toString()
    }

    const closeViewProgramSchedule = () => {
      const url = new URL(window.location.href)
      url.searchParams.delete('action')
      url.searchParams.delete('schedule_id')
      // showViewScreen.value = false
      window.location.href = url.toString()
    }
    const goToEditProgramSchedule = (programScheduleId: string) => {
      const url = new URL(window.location.href)
      url.searchParams.set('action', 'add_programSchedule')
      url.searchParams.set('schedule_id', programScheduleId)
      // showViewScreen.value = true
      window.location.href = url.toString()
    }

    const closeEditProgramSchedule = () => {
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

    const getOneProgramSchedule = (id: string) => {
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
          currentProgramSchedule.value = response.data
        })
        .catch((error) => {
          console.error('Error:', error)
        })
        .finally(() => {
          loading.get = false
        })
    }

    const createProgramSchedule = (args: Partial<ProgramSchedule>[]) => {
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
          currentProgramSchedule.value = response.data
          // showAddForm.value = false
          toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Program Schedule saved successfully',
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

    const updateProgramSchedule = (args: Partial<ProgramSchedule>) => {
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
          getOneProgramSchedule(programScheduleId.value as string)
          toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Program Schedule updated successfully',
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

    const deleteProgramSchedule = (id: string) => {
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
          fetchProgramSchedules()
        })
    }

    return {
      programSchedules: computed(() => programSchedules),
      fetchProgramSchedules,
      filter: computed(() => filter),
      loading: computed(() => loading),
      currentProgramSchedule: computed(() => currentProgramSchedule),
      goToViewProgramSchedule,
      closeViewProgramSchedule,
      programScheduleId,
      getOneProgramSchedule,
      showAddForm,
      showViewScreen,
      showEditScreen,
      goToAddForm,
      closeAddForm,
      createProgramSchedule,
      updateProgramSchedule,
      deleteProgramSchedule,
      goToEditProgramSchedule,
      closeEditProgramSchedule
    }
  })()
