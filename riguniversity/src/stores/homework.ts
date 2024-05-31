import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'

export interface Homework {
  name: string
  homework_id: number
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
    // window.history.replaceState({}, document.title, url.toString())
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

  return {
    homeworks: computed(() => homeworks),
    fetchHomeworks,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentHomework: computed(() => currentHomework),
    goToViewHomework,
    homeworkId,
    getOneHomework
  }
})
