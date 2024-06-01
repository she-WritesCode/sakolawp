import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'

export interface Homework {
  name: string
  subject_id: number
}

export const useHomeworkStore = defineStore('homework', () => {
  const homeworks = ref<Homework[]>([])
  const currentHomework = ref<Homework | null>(null)
  const filter = reactive({
    search: '',
    subject_id: ''
  })
  const loading = ref(false)
  const homeworkId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('homework_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = ref(action.value === 'add_homework')

  watch(filter, () => {
    fetchHomeworks()
  })

  const fetchHomeworks = () => {
    loading.value = true
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
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewHomework = (homeworkId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('homework_id', homeworkId)
    window.location.href = url.toString()
  }

  const getOneHomework = (id: string) => {
    loading.value = true
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
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_homework')
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    window.location.href = url.toString()
  }

  const createHomework = (args: Partial<Homework>) => {
    loading.value = true
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
      })
      .finally(() => {
        loading.value = false
      })
  }

  const updateHomework = (args: Partial<Homework>) => {
    // loading.value = true
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
        loading.value = false
      })
  }

  const deleteHomework = (id: string) => {
    loading.value = true
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
    homeworkId,
    getOneHomework,
    showAddForm,
    goToAddForm,
    closeAddForm,
    createHomework,
    updateHomework,
    deleteHomework
  }
})
