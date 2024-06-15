import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface Program {
  class_id?: string
  name?: string
}

export const useProgramStore = defineStore('program', () => {
  const toast = useToast()
  const programs = ref<Program[]>([])
  const currentProgram = ref<Program | undefined>(undefined)
  const filter = reactive({
    search: '',
    subject_id: ''
  })
  const loading = reactive({ list: false, get: false, create: false, update: false, delete: false })
  const programId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('class_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_program')
  const showViewScreen = computed(() => action.value === 'view_program' && !!programId.value)
  const showEditScreen = computed(() => action.value === 'add_program' && !!programId.value)

  watch(filter, () => {
    fetchPrograms()
  })

  const fetchPrograms = () => {
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
        programs.value = response.data
        loading.list = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewProgram = (programId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_program')
    url.searchParams.set('class_id', programId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewProgram = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditProgram = (programId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_program')
    url.searchParams.set('class_id', programId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditProgram = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', programId.value as string)
    url.searchParams.set('action', 'view_program')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_program')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneProgram = (id: string) => {
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
        currentProgram.value = response.data
        loading.get = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const createProgram = (args: Partial<Program>) => {
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
        currentProgram.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Program created successfully',
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

  const updateProgram = (args: Partial<Program>) => {
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
        getOneProgram(programId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Program updated successfully',
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

  const deleteProgram = (id: string) => {
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
        fetchPrograms()
      })
  }

  return {
    programs: computed(() => programs),
    fetchPrograms,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentProgram: computed(() => currentProgram),
    goToViewProgram,
    closeViewProgram,
    programId,
    getOneProgram,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createProgram,
    updateProgram,
    deleteProgram,
    goToEditProgram,
    closeEditProgram
  }
})
