import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface ProgramEnrollment {
  accountability_id: string
  accountability_name: string
  class_id: string
  class_name: string
  created_at: string
  date_added: string
  enroll_code: string
  enroll_id: string
  roll: string
  section_id: string
  section_name: string
  student_email: string
  student_id: string
  student_name: string
  updated_at: string
  year: string
}

export const useProgramEnrollmentStore = defineStore('programEnrollment', () => {
  const toast = useToast()
  const programEnrollments = ref<ProgramEnrollment[]>([])
  const currentProgramEnrollment = ref<ProgramEnrollment | undefined>(undefined)
  const filter = reactive({
    search: '',
    class_id: ''
  })
  const loading = reactive({
    list: false,
    get: false,
    create: false,
    update: false,
    delete: false
  })
  const programEnrollmentId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_programEnrollment')
  const showViewScreen = computed(
    () => action.value === 'view_programEnrollment' && !!programEnrollmentId.value
  )
  const showEditScreen = computed(
    () => action.value === 'add_programEnrollment' && !!programEnrollmentId.value
  )

  watch(filter, () => {
    fetchProgramEnrollments()
  })

  const fetchProgramEnrollments = () => {
    if (!filter.search) loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_enrolls',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        programEnrollments.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const fetchCurrentUserEnrollments = () => {
    if (!filter.search) loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_current_user_enrolls',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        programEnrollments.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewProgramEnrollment = (programEnrollmentId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_programEnrollment')
    url.searchParams.set('class_id', programEnrollmentId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewProgramEnrollment = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditProgramEnrollment = (programEnrollmentId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programEnrollment')
    url.searchParams.set('class_id', programEnrollmentId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditProgramEnrollment = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', programEnrollmentId.value as string)
    url.searchParams.set('action', 'view_programEnrollment')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programEnrollment')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneProgramEnrollment = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_enroll',
        class_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentProgramEnrollment.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createProgramEnrollment = (args: Partial<ProgramEnrollment>) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_create_enroll',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentProgramEnrollment.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programEnrollment created successfully',
          life: 3000
        })
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        closeAddForm()
        loading.create = false
      })
  }

  const updateProgramEnrollment = (args: Partial<ProgramEnrollment>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_enroll',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneProgramEnrollment(programEnrollmentId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programEnrollment updated successfully',
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

  const deleteProgramEnrollment = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_enroll',
        class_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchProgramEnrollments()
      })
  }

  return {
    programEnrollments: computed(() => programEnrollments),
    fetchProgramEnrollments,
    fetchCurrentUserEnrollments,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentProgramEnrollment: computed(() => currentProgramEnrollment),
    goToViewProgramEnrollment,
    closeViewProgramEnrollment,
    programEnrollmentId,
    getOneProgramEnrollment,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createProgramEnrollment,
    updateProgramEnrollment,
    deleteProgramEnrollment,
    goToEditProgramEnrollment,
    closeEditProgramEnrollment
  }
})
