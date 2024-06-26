import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import type { Question } from './form'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams, convertObjectToFormData } from '@/utils/search'
import { useUrlParamsStore } from './urlparams'

export interface Homework {
  homework_id?: string
  homework_code?: string
  title: string
  subject_id: string
  description: string
  file_name?: any
  file_date?: string
  file_url?: string
  file_id?: string
  allow_peer_review: boolean
  peer_review_template: string
  peer_review_who: string
  word_count_min: null
  word_count_max: null
  limit_word_count: boolean
  date_end: Date
  time_end: Date
  questions: Question[]
  delivery_count?: number
}

export const useHomeworkStore = defineStore('homework', () => {
  const toast = useToast()
  const homeworks = ref<Homework[]>([])
  const currentHomework = ref<Homework | undefined>(undefined)
  const filter = reactive({
    search: '',
    subject_id: ''
  })
  const loading = reactive({
    list: false,
    get: false,
    create: false,
    duplicate: false,
    update: false,
    delete: false
  })
  const { urlParams } = useUrlParamsStore()

  const homeworkId = computed(() => urlParams.value.get('homework_id'))
  const action = computed(() => urlParams.value.get('rig_action'))

  const showAddForm = computed(() => action.value === 'add_homework')
  const showViewScreen = computed(() => action.value === 'view_homework' && !!homeworkId.value)
  const showEditScreen = computed(() => action.value === 'add_homework' && !!homeworkId.value)

  watch(filter, () => {
    fetchHomeworks()
  })

  const fetchHomeworks = () => {
    loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_homeworks',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        homeworks.value = response.data
        loading.list = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewHomework = (homeworkId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('rig_action', 'view_homework')
    url.searchParams.set('homework_id', homeworkId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewHomework = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    url.searchParams.delete('homework_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToEditHomework = (homeworkId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('rig_action', 'add_homework')
    url.searchParams.set('homework_id', homeworkId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditHomework = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    url.searchParams.set('homework_id', homeworkId.value as string)
    url.searchParams.set('rig_action', 'view_homework')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('rig_action', 'add_homework')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneHomework = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_homework',
        homework_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentHomework.value = response.data
        loading.get = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const createHomework = (args: Partial<Homework>) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      body: convertObjectToFormData({
        action: 'run_create_homework',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        // currentHomework.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Assessment created successfully',
          life: 3000
        })
        // if (response.data.result) {
        //   goToEditHomework(response.data.result)
        // } else {
        closeAddForm()
        // }
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        loading.create = false
      })
  }
  const duplicate = (homework_id: string, title: string = '') => {
    loading.duplicate = true
    // @ts-ignore
    return fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_duplicate_homework',
        homework_id,
        title
      })
    })
      .then((response) => response.json())
      .then((response) => {
        // currentHomework.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Assessment duplicated successfully',
          life: 3000
        })
        fetchHomeworks()
        return new Promise((resolve) => {
          resolve(response)
        })
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        loading.duplicate = false
      })
  }

  const updateHomework = (args: Partial<Homework>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      body: convertObjectToFormData({
        action: 'run_update_homework',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneHomework(homeworkId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Assessment updated successfully',
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

  const deleteHomework = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_homework',
        homework_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchHomeworks()
      })
  }

  return {
    homeworks: computed(() => homeworks),
    fetchHomeworks,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentHomework: computed(() => currentHomework),
    goToViewHomework,
    closeViewHomework,
    homeworkId,
    getOneHomework,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createHomework,
    updateHomework,
    deleteHomework,
    goToEditHomework,
    closeEditHomework,
    duplicate
  }
})
