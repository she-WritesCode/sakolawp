import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'
import type { Homework } from './homework'
import type { Subject } from './subject'

export interface programSubject extends Subject {
  homeworks?: Homework[]
}

export const useprogramSubjectStore = defineStore('programSubject', () => {
  const toast = useToast()
  const programSubjects = ref<programSubject[]>([])
  const currentprogramSubject = ref<programSubject | undefined>(undefined)
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
  const programSubjectId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_programSubject')
  const showViewScreen = computed(
    () => action.value === 'view_programSubject' && !!programSubjectId.value
  )
  const showEditScreen = computed(
    () => action.value === 'add_programSubject' && !!programSubjectId.value
  )

  watch(filter, () => {
    fetchprogramSubjects()
  })

  const fetchprogramSubjects = () => {
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
        programSubjects.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewprogramSubject = (programSubjectId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_programSubject')
    url.searchParams.set('subject_id', programSubjectId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewprogramSubject = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('subject_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditprogramSubject = (programSubjectId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programSubject')
    url.searchParams.set('subject_id', programSubjectId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditprogramSubject = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('subject_id', programSubjectId.value as string)
    url.searchParams.set('action', 'view_programSubject')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programSubject')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneprogramSubject = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_class',
        subject_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentprogramSubject.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createprogramSubject = (args: Partial<programSubject>) => {
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
        currentprogramSubject.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programSubject created successfully',
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

  const updateprogramSubject = (args: Partial<programSubject>) => {
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
        getOneprogramSubject(programSubjectId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programSubject updated successfully',
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

  const deleteprogramSubject = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_class',
        subject_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchprogramSubjects()
      })
  }

  return {
    programSubjects: computed(() => programSubjects),
    fetchprogramSubjects,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentprogramSubject: computed(() => currentprogramSubject),
    goToViewprogramSubject,
    closeViewprogramSubject,
    programSubjectId,
    getOneprogramSubject,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createprogramSubject,
    updateprogramSubject,
    deleteprogramSubject,
    goToEditprogramSubject,
    closeEditprogramSubject
  }
})
