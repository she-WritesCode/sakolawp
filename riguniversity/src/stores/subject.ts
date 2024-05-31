import { ref, computed, watch } from 'vue'
import { defineStore } from 'pinia'

export interface Subject {
  name: string
  subject_id: number
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

  return {
    subjects: computed(() => subjects),
    fetchSubjects,
    search: computed(() => search),
    loading: computed(() => loading),
    currentSubject: computed(() => currentSubject),
    goToViewSubject,
    subjectId,
    getOneSubject
  }
})
