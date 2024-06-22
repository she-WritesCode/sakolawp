import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'
import { useUrlParamsStore } from './urlparams'

export interface CreateLesson {
  title: string
  meta: Record<string, string>
}
export interface Lesson {
  title: string
  content: string
  excerpt: string
  permalink: string
  meta: Record<string, string[]>
  ID?: string
  teacher_id: string
  class_id: string
  author: string
}

export const useLessonStore = defineStore('lesson', () => {
  const toast = useToast()
  const lessons = ref<Lesson[]>([])
  const currentLesson = ref<Lesson | undefined>(undefined)
  const filter = reactive<{
    search: string
    meta_query: { key: string; value: string; compare: string }[]
  }>({
    search: '',
    meta_query: []
  })
  const loading = reactive({ list: false, get: false, create: false, update: false, delete: false })
  const { urlParams } = useUrlParamsStore()

  const lessonId = computed(() => urlParams.value.get('lesson_id'))
  const action = computed(() => urlParams.value.get('rig_action'))

  const showAddForm = computed(() => action.value === 'add_lesson')
  const showViewScreen = computed(() => action.value === 'view_lesson' && !!lessonId.value)
  const showEditScreen = computed(() => action.value === 'add_lesson' && !!lessonId.value)

  watch(filter, () => {
    fetchLessons()
  })

  const fetchLessons = () => {
    loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_list_lessons',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        lessons.value = response.data
        loading.list = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewLesson = (lessonId: string) => {
    // const url = new URL(window.location.href)
    // url.searchParams.set('rig_action', 'view_lesson')
    // url.searchParams.set('lesson_id', lessonId)
    // // showViewScreen.value = true
    // window.location.href = url.toString()
  }

  const closeViewLesson = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    url.searchParams.delete('lesson_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditLesson = (lessonId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('rig_action', 'add_lesson')
    url.searchParams.set('lesson_id', lessonId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditLesson = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    url.searchParams.set('lesson_id', lessonId.value as string)
    url.searchParams.set('rig_action', 'view_lesson')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('rig_action', 'add_lesson')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneLesson = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_lesson',
        lesson_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentLesson.value = response.data
        loading.get = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const createLesson = (args: CreateLesson) => {
    loading.create = true
    // @ts-ignore
    return fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_create_lesson',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentLesson.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Lesson created successfully',
          life: 3000
        })
        fetchLessons()
        return new Promise((resolve) => {
          resolve(true)
        })
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
        return new Promise((resolve) => {
          resolve(false)
        })
      })
      .finally(() => {
        loading.create = false
      })
  }

  const updateLesson = (args: Partial<Lesson>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_lesson',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneLesson(lessonId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Lesson updated successfully',
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

  const deleteLesson = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_lesson',
        lesson_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchLessons()
      })
  }

  return {
    lessons: computed(() => lessons),
    fetchLessons,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentLesson: computed(() => currentLesson),
    goToViewLesson,
    closeViewLesson,
    lessonId,
    getOneLesson,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createLesson,
    updateLesson,
    deleteLesson,
    goToEditLesson,
    closeEditLesson
  }
})
