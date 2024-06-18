import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface CohortMeeting {
  ID?: number
  content?: string
  excerpt?: string
  permalink?: string
  date?: string
  author?: string
  meta?: Record<string, string[]>
}

export const useCohortMeetingStore = defineStore('cohortMeeting', () => {
  const toast = useToast()
  const cohortMeetings = ref<CohortMeeting[]>([])
  const currentCohortMeeting = ref<CohortMeeting | undefined>(undefined)
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
  const cohortMeetingId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_cohortMeeting')
  const showViewScreen = computed(
    () => action.value === 'view_cohortMeeting' && !!cohortMeetingId.value
  )
  const showEditScreen = computed(
    () => action.value === 'add_cohortMeeting' && !!cohortMeetingId.value
  )

  watch(filter, () => {
    fetchCohortMeetings()
  })

  const fetchCohortMeetings = () => {
    if (!filter.search) loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_events',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        cohortMeetings.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewCohortMeeting = (cohortMeetingId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_cohortMeeting')
    url.searchParams.set('class_id', cohortMeetingId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewCohortMeeting = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditCohortMeeting = (cohortMeetingId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortMeeting')
    url.searchParams.set('class_id', cohortMeetingId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditCohortMeeting = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', cohortMeetingId.value as string)
    url.searchParams.set('action', 'view_cohortMeeting')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortMeeting')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneCohortMeeting = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_event',
        class_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohortMeeting.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createCohortMeeting = (args: Partial<CohortMeeting>) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_create_event',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohortMeeting.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortMeeting created successfully',
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

  const updateCohortMeeting = (args: Partial<CohortMeeting>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_event',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneCohortMeeting(cohortMeetingId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortMeeting updated successfully',
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

  const deleteCohortMeeting = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_event',
        class_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchCohortMeetings()
      })
  }

  return {
    cohortMeetings: computed(() => cohortMeetings),
    fetchCohortMeetings,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentCohortMeeting: computed(() => currentCohortMeeting),
    goToViewCohortMeeting,
    closeViewCohortMeeting,
    cohortMeetingId,
    getOneCohortMeeting,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createCohortMeeting,
    updateCohortMeeting,
    deleteCohortMeeting,
    goToEditCohortMeeting,
    closeEditCohortMeeting
  }
})
