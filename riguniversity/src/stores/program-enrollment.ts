import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface programEnrollment {
  ID?: number
  content?: string
  excerpt?: string
  permalink?: string
  date?: string
  author?: string
  meta?: Record<string, string[]>
}

export const useprogramEnrollmentStore = defineStore('programEnrollment', () => {
  const toast = useToast()
  const programEnrollments = ref<programEnrollment[]>([])
  const currentprogramEnrollment = ref<programEnrollment | undefined>(undefined)
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
    fetchprogramEnrollments()
  })

  const fetchprogramEnrollments = () => {
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

  const goToViewprogramEnrollment = (programEnrollmentId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_programEnrollment')
    url.searchParams.set('class_id', programEnrollmentId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewprogramEnrollment = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditprogramEnrollment = (programEnrollmentId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programEnrollment')
    url.searchParams.set('class_id', programEnrollmentId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditprogramEnrollment = () => {
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

  const getOneprogramEnrollment = (id: string) => {
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
        currentprogramEnrollment.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createprogramEnrollment = (args: Partial<programEnrollment>) => {
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
        currentprogramEnrollment.value = response.data
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

  const updateprogramEnrollment = (args: Partial<programEnrollment>) => {
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
        getOneprogramEnrollment(programEnrollmentId.value as string)
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

  const deleteprogramEnrollment = (id: string) => {
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
        fetchprogramEnrollments()
      })
  }

  return {
    programEnrollments: computed(() => programEnrollments),
    fetchprogramEnrollments,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentprogramEnrollment: computed(() => currentprogramEnrollment),
    goToViewprogramEnrollment,
    closeViewprogramEnrollment,
    programEnrollmentId,
    getOneprogramEnrollment,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createprogramEnrollment,
    updateprogramEnrollment,
    deleteprogramEnrollment,
    goToEditprogramEnrollment,
    closeEditprogramEnrollment
  }
})
