import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface CohortEnrollment {
  ID?: number
  content?: string
  excerpt?: string
  permalink?: string
  date?: string
  author?: string
  meta?: Record<string, string[]>
}

export const useCohortEnrollmentStore = defineStore('cohortEnrollment', () => {
  const toast = useToast()
  const cohortEnrollments = ref<CohortEnrollment[]>([])
  const currentCohortEnrollment = ref<CohortEnrollment | undefined>(undefined)
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
  const cohortEnrollmentId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_cohortEnrollment')
  const showViewScreen = computed(
    () => action.value === 'view_cohortEnrollment' && !!cohortEnrollmentId.value
  )
  const showEditScreen = computed(
    () => action.value === 'add_cohortEnrollment' && !!cohortEnrollmentId.value
  )

  watch(filter, () => {
    fetchCohortEnrollments()
  })

  const fetchCohortEnrollments = () => {
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
        cohortEnrollments.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewCohortEnrollment = (cohortEnrollmentId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_cohortEnrollment')
    url.searchParams.set('class_id', cohortEnrollmentId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewCohortEnrollment = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditCohortEnrollment = (cohortEnrollmentId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortEnrollment')
    url.searchParams.set('class_id', cohortEnrollmentId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditCohortEnrollment = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', cohortEnrollmentId.value as string)
    url.searchParams.set('action', 'view_cohortEnrollment')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortEnrollment')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneCohortEnrollment = (id: string) => {
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
        currentCohortEnrollment.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createCohortEnrollment = (args: Partial<CohortEnrollment>) => {
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
        currentCohortEnrollment.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortEnrollment created successfully',
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

  const updateCohortEnrollment = (args: Partial<CohortEnrollment>) => {
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
        getOneCohortEnrollment(cohortEnrollmentId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortEnrollment updated successfully',
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

  const deleteCohortEnrollment = (id: string) => {
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
        fetchCohortEnrollments()
      })
  }

  return {
    cohortEnrollments: computed(() => cohortEnrollments),
    fetchCohortEnrollments,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentCohortEnrollment: computed(() => currentCohortEnrollment),
    goToViewCohortEnrollment,
    closeViewCohortEnrollment,
    cohortEnrollmentId,
    getOneCohortEnrollment,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createCohortEnrollment,
    updateCohortEnrollment,
    deleteCohortEnrollment,
    goToEditCohortEnrollment,
    closeEditCohortEnrollment
  }
})
