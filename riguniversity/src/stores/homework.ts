import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import type { Question } from './form'
import useToast from 'primevue/toast'

export interface Homework {
  homework_id?: string
  homework_code?: string
  title: string
  subject_id: string
  description: string
  file_name: string
  allow_peer_review: boolean
  peer_review_template: string
  peer_review_who: string
  word_count_min: null
  word_count_max: null
  limit_word_count: boolean
  date_end: Date
  time_end: string
  responses: Question[]
}

export const useHomeworkStore = defineStore('homework', () => {
  // const toast = useToast()
  const homeworks = ref<Homework[]>([])
  const currentHomework = ref<Homework | undefined>(undefined)
  const filter = reactive({
    search: '',
    subject_id: ''
  })
  const loading = reactive({ list: false, get: false, create: false, update: false, delete: false })
  const homeworkId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('homework_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = ref(action.value === 'add_homework')
  const showViewScreen = ref(action.value === 'view_homework' && !!homeworkId.value)
  const showEditScreen = ref(action.value === 'add_homework' && !!homeworkId.value)

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
    url.searchParams.set('action', 'view_homework')
    url.searchParams.set('homework_id', homeworkId)
    showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewHomework = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('homework_id')
    showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditHomework = (homeworkId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_homework')
    url.searchParams.set('homework_id', homeworkId)
    showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditHomework = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('homework_id', homeworkId.value as string)
    url.searchParams.set('action', 'view_homework')
    showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_homework')
    showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    showAddForm.value = false
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
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_create_homework',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentHomework.value = response.data
        showAddForm.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
        // TODO Toast
      })
      .finally(() => {
        closeAddForm()
        loading.create = false
      })
  }

  const updateHomework = (args: Partial<Homework>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_update_homework',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneHomework(homeworkId.value as string)
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
    showAddForm: computed(() => showAddForm),
    showViewScreen: computed(() => showViewScreen),
    showEditScreen: computed(() => showEditScreen),
    goToAddForm,
    closeAddForm,
    createHomework,
    updateHomework,
    deleteHomework,
    goToEditHomework,
    closeEditHomework
  }
})
