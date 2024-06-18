import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface CohortSubject {
  class_id?: string
  name?: string
}

export const useCohortSubjectStore = defineStore('cohortSubject', () => {
  const toast = useToast()
  const cohortSubjects = ref<CohortSubject[]>([])
  const currentCohortSubject = ref<CohortSubject | undefined>(undefined)
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
  const cohortSubjectId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_cohortSubject')
  const showViewScreen = computed(
    () => action.value === 'view_cohortSubject' && !!cohortSubjectId.value
  )
  const showEditScreen = computed(
    () => action.value === 'add_cohortSubject' && !!cohortSubjectId.value
  )

  watch(filter, () => {
    fetchCohortSubjects()
  })

  const fetchCohortSubjects = () => {
    loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_class_subjects',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        cohortSubjects.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewCohortSubject = (cohortSubjectId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_cohortSubject')
    url.searchParams.set('class_id', cohortSubjectId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewCohortSubject = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditCohortSubject = (cohortSubjectId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortSubject')
    url.searchParams.set('class_id', cohortSubjectId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditCohortSubject = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', cohortSubjectId.value as string)
    url.searchParams.set('action', 'view_cohortSubject')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortSubject')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneCohortSubject = (id: string) => {
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
        currentCohortSubject.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createCohortSubject = (args: Partial<CohortSubject>) => {
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
        currentCohortSubject.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortSubject created successfully',
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

  const updateCohortSubject = (args: Partial<CohortSubject>) => {
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
        getOneCohortSubject(cohortSubjectId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortSubject updated successfully',
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

  const deleteCohortSubject = (id: string) => {
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
        fetchCohortSubjects()
      })
  }

  return {
    cohortSubjects: computed(() => cohortSubjects),
    fetchCohortSubjects,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentCohortSubject: computed(() => currentCohortSubject),
    goToViewCohortSubject,
    closeViewCohortSubject,
    cohortSubjectId,
    getOneCohortSubject,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createCohortSubject,
    updateCohortSubject,
    deleteCohortSubject,
    goToEditCohortSubject,
    closeEditCohortSubject
  }
})
