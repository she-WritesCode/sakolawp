import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface Cohort {
  class_id?: string
  name?: string
}

export const useCohortStore = defineStore('cohort', () => {
  const toast = useToast()
  const cohorts = ref<Cohort[]>([])
  const currentCohort = ref<Cohort | undefined>(undefined)
  const filter = reactive({
    search: '',
    subject_id: ''
  })
  const loading = reactive({ list: false, get: false, create: false, update: false, delete: false })
  const cohortId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('class_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_cohort')
  const showViewScreen = computed(() => action.value === 'view_cohort' && !!cohortId.value)
  const showEditScreen = computed(() => action.value === 'add_cohort' && !!cohortId.value)

  watch(filter, () => {
    fetchCohorts()
  })

  const fetchCohorts = () => {
    loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_class',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        cohorts.value = response.data
        loading.list = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewCohort = (cohortId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_cohort')
    url.searchParams.set('class_id', cohortId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewCohort = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditCohort = (cohortId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohort')
    url.searchParams.set('class_id', cohortId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditCohort = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', cohortId.value as string)
    url.searchParams.set('action', 'view_cohort')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohort')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneCohort = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_class',
        class_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohort.value = response.data
        loading.get = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const createCohort = (args: Partial<Cohort>) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_create_class',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohort.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Cohort created successfully',
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

  const updateCohort = (args: Partial<Cohort>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_class',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneCohort(cohortId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Cohort updated successfully',
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

  const deleteCohort = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_class',
        class_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchCohorts()
      })
  }

  return {
    cohorts: computed(() => cohorts),
    fetchCohorts,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentCohort: computed(() => currentCohort),
    goToViewCohort,
    closeViewCohort,
    cohortId,
    getOneCohort,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createCohort,
    updateCohort,
    deleteCohort,
    goToEditCohort,
    closeEditCohort
  }
})
