import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export interface Subject {
  name: string
  subject_id: number
}

export const useSubjectStore = defineStore('subject', () => {
  const subjects = ref<Subject[]>([])
  const search = ref('')

  const fetchSubjects = () => {
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_subjects',
        section_id: ''
      })
    })
      .then((response) => response.json())
      .then((response) => {
        subjects.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  return { subjects: computed(() => subjects), fetchSubjects, search: computed(() => search) }
})
