import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import type { Question } from './form'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams, convertObjectToFormData } from '@/utils/search'
import { useUrlParamsStore } from './urlparams'

export interface PeerReview {
  peer_review_id?: string
  title: string
  homework_id: string
  description: string
  file_name?: any
  file_date?: string
  file_url?: string
  file_id?: string
  allow_peer_review: boolean
  peer_review_template: string
  peer_review_who: string
  word_count_min: null
  word_count_max: null
  limit_word_count: boolean
  date_end: Date
  time_end: Date
  questions: Question[]
  delivery_count?: number
}

export const usePeerReviewStore = defineStore('peerReview', () => {
  const toast = useToast()
  const peerReviews = ref<PeerReview[]>([])
  const currentPeerReview = ref<PeerReview | undefined>(undefined)
  const filter = reactive({
    search: '',
    homework_id: '',
    review_id: '',
    peer_id: '',
    delivery_id: '',
    class_id: ''
  })
  const loading = reactive({
    list: false,
    get: false,
    create: false,
    duplicate: false,
    update: false,
    delete: false
  })
  const { urlParams } = useUrlParamsStore()

  const peerReviewId = computed(() => urlParams.value.get('peer_review_id'))
  const action = computed(() => urlParams.value.get('rig_action'))

  const showAddForm = computed(() => action.value === 'add_peer_review')
  const showViewScreen = computed(() => action.value === 'view_peer_review' && !!peerReviewId.value)
  const showEditScreen = computed(() => action.value === 'add_peer_review' && !!peerReviewId.value)

  watch(filter, () => {
    fetchPeerReviews()
  })

  const fetchPeerReviews = () => {
    loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_peer_reviews',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        peerReviews.value = response.data
        loading.list = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewPeerReview = (peerReviewId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('rig_action', 'view_peer_review')
    url.searchParams.set('peer_review_id', peerReviewId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewPeerReview = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    url.searchParams.delete('peer_review_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToEditPeerReview = (peerReviewId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('rig_action', 'add_peer_review')
    url.searchParams.set('peer_review_id', peerReviewId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditPeerReview = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    url.searchParams.set('peer_review_id', peerReviewId.value as string)
    url.searchParams.set('rig_action', 'view_peer_review')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('rig_action', 'add_peer_review')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('rig_action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOnePeerReview = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_peer_review',
        peer_review_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentPeerReview.value = response.data
        loading.get = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const createPeerReview = (args: Partial<PeerReview>) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      body: convertObjectToFormData({
        action: 'run_create_peer_review',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        // currentPeerReview.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Assessment created successfully',
          life: 3000
        })
        // if (response.data.result) {
        //   goToEditPeerReview(response.data.result)
        // } else {
        closeAddForm()
        // }
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        loading.create = false
      })
  }
  const duplicate = (peer_review_id: string, title: string = '') => {
    loading.duplicate = true
    // @ts-ignore
    return fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_duplicate_peer_review',
        peer_review_id,
        title
      })
    })
      .then((response) => response.json())
      .then((response) => {
        // currentPeerReview.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Assessment duplicated successfully',
          life: 3000
        })
        fetchPeerReviews()
        return new Promise((resolve) => {
          resolve(response)
        })
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        loading.duplicate = false
      })
  }

  const updatePeerReview = (args: Partial<PeerReview>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      body: convertObjectToFormData({
        action: 'run_update_peer_review',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOnePeerReview(peerReviewId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Assessment updated successfully',
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

  const deletePeerReview = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_peer_review',
        peer_review_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchPeerReviews()
      })
  }

  return {
    peerReviews: computed(() => peerReviews),
    fetchPeerReviews,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentPeerReview: computed(() => currentPeerReview),
    goToViewPeerReview,
    closeViewPeerReview,
    peerReviewId,
    getOnePeerReview,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createPeerReview,
    updatePeerReview,
    deletePeerReview,
    goToEditPeerReview,
    closeEditPeerReview,
    duplicate
  }
})
