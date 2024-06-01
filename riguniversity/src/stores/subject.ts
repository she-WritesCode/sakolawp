import { ref, computed, watch } from 'vue'
import { defineStore } from 'pinia'

export interface Subject {
  name: string
  subject_id: string
}

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

  const showAddFrom = computed({
    get: () => {
      return action.value === 'add_subject'
    },
    set: (value) => {
      if (value) {
        goToAddForm()
      } else {
        window.history.back()
      }
    }
  })

  watch(search, () => {
    fetchSubjects()
  })

  const fetchSubjects = () => {
    loading.value = true
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
    // window.history.replaceState({}, document.title, url.toString())
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_subject')
    window.history.pushState({}, document.title, url.toString())
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
        ...args
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentSubject.value = response.data
        loading.value = false
        showAddFrom.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
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
    showAddFrom,
    action: computed(() => action),
    createSubject
  }
})
