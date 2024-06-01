import { ref, computed, watch } from 'vue'
import { defineStore } from 'pinia'
import * as yup from 'yup'

export interface Subject {
  name: string
  subject_id?: string
  teacher_id: string
  teacher_name: string
  lesson_count: number
  homework_count: number
}

export const createSubjectSchema = yup.object({
  name: yup.string().min(3).required(),
  teacher_id: yup.string().optional()
})

export const useSubjectStore = defineStore('subject', () => {
  const subjects = ref<Subject[]>([])
  const currentSubject = ref<Subject | null>(null)
  const search = ref('')
  const loading = ref(false)
  const subjectId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = ref(action.value === 'add_subject')
  watch(showAddForm, (value) => {
    if (!value) {
      window.history.back()
    }
  })

  watch(search, () => {
    fetchSubjects()
  })

  const fetchSubjects = () => {
    if (!search.value) {
      loading.value = true
    }
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_subjects',
        search: search.value
      })
    })
      .then((response) => response.json())
      .then((response) => {
        subjects.value = response.data
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewSubject = (subjectId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('subject_id', subjectId)
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_subject')
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    window.location.href = url.toString()
  }

  const getOneSubject = (id: string) => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_subject',
        subject_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentSubject.value = response.data
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const createSubject = (args: Partial<Subject>) => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_create_subject',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentSubject.value = response.data
        showAddForm.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.value = false
      })
  }

  const deleteSubject = (id: string) => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_subject',
        subject_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        fetchSubjects()
      })
  }
  return {
    subjects: computed(() => subjects),
    fetchSubjects,
    search: computed(() => search),
    loading: computed(() => loading),
    currentSubject: computed(() => currentSubject),
    goToViewSubject,
    subjectId,
    getOneSubject,
    goToAddForm,
    showAddForm,
    action: computed(() => action),
    createSubject,
    closeAddForm,
    deleteSubject
  }
})
