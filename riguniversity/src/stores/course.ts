import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import * as yup from 'yup'

export interface Course {
  title: string
  content: string
  excerpt: string
  permalink: string
  meta: Record<string, string[]>
  ID?: number
  teacher_id: string
  class_id: string
  author: string
  lesson_count: number
  homework_count: number
}

export const createCourseSchema = yup.object({
  name: yup.string().min(3).required(),
  teacher_id: yup.string().optional()
})

export const useCourseStore = defineStore('course', () => {
  const courses = ref<Course[]>([])
  const currentCourse = ref<Course | null>(null)
  const filter = reactive({ search: '' })
  const loading = ref(false)
  const courseId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('post') || undefined
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = ref(action.value === 'add_course')
  watch(showAddForm, (value) => {
    if (!value) {
      window.history.back()
    }
  })

  watch(filter, () => {
    fetchCourses()
  })

  const fetchCourses = () => {
    if (!filter.search) {
      loading.value = true
    }
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_courses',
        search: filter.search
      })
    })
      .then((response) => response.json())
      .then((response) => {
        courses.value = response.data
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewCourse = (courseId: string) => {
    // const url = new URL(window.location.href)
    // url.searchParams.set('course_id', courseId)
    // window.location.href = url.toString()
  }

  const goToAddForm = () => {
    // const url = new URL(window.location.href)
    // url.searchParams.set('action', 'add_course')
    // window.location.href = url.toString()
  }

  const closeAddForm = () => {
    // const url = new URL(window.location.href)
    // url.searchParams.delete('action')
    // window.location.href = url.toString()
  }

  const getOneCourse = (id: string) => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_course',
        course_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCourse.value = response.data
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const createCourse = (args: Partial<Course>) => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_create_course',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCourse.value = response.data
        showAddForm.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.value = false
      })
  }

  const updateCourse = (args: Partial<Course>) => {
    // loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_update_course',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        getOneCourse(courseId.value as string)
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.value = false
      })
  }

  const deleteCourse = (id: string) => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_course',
        course_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        fetchCourses()
      })
  }
  return {
    courses: computed(() => courses),
    fetchCourses,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentCourse: computed(() => currentCourse),
    goToViewCourse,
    courseId,
    getOneCourse,
    goToAddForm,
    showAddForm,
    action: computed(() => action),
    createCourse,
    closeAddForm,
    deleteCourse,
    updateCourse
  }
})
