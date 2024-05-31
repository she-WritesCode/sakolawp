import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'

export interface Lesson {
  name: string
  lesson_id: number
}

export const useLessonStore = defineStore('lesson', () => {
  const lessons = ref<Lesson[]>([])
  const currentLesson = ref<Lesson | null>(null)
  const filter = reactive({
    search: '',
    subject_id: ''
  })
  const loading = ref(false)
  const lessonId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('lesson_id')
  })

  watch(filter, () => {
    fetchLessons()
  })

  const fetchLessons = () => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_lessons',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        lessons.value = response.data
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewLesson = (lessonId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('lesson_id', lessonId)
    window.location.href = url.toString()
  }

  const getOneLesson = (id: string) => {
    loading.value = true
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
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  return {
    lessons: computed(() => lessons),
    fetchLessons,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentLesson: computed(() => currentLesson),
    goToViewLesson,
    lessonId,
    getOneLesson
  }
})
