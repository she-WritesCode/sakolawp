import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { convertObjectToSearchParams } from '@/utils/search'
import { useHomeworkStore } from './homework'

export interface HomeworkDelivery {
  delivery_id?: string
}

export const useHomeworkDeliveryStore = defineStore('homeworkDeliveries', () => {
  // const toast = useToast()
  const deliveries = ref<HomeworkDelivery[]>([])
  const currentDelivery = ref<HomeworkDelivery | undefined>(undefined)
  const filter = reactive({
    search: '',
    homework_code: ''
  })
  const loading = reactive({ list: false, get: false, create: false, update: false, delete: false })

  const deliveryId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('delivery_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('sub_action')
  })

  const showViewScreen = computed(() => action.value === 'view_delivery' && !!deliveryId.value)
  const showEditScreen = computed(() => action.value === 'edit_delivery' && !!deliveryId.value)

  watch(filter, () => {
    fetchHomeworkDeliveries()
  })

  const fetchHomeworkDeliveries = () => {
    loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_deliveries',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        deliveries.value = response.data
        loading.list = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewHomeworkDelivery = (deliveryId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('sub_action', 'view_delivery')
    url.searchParams.set('delivery_id', deliveryId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewHomeworkDelivery = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('sub_action')
    url.searchParams.delete('delivery_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditHomeworkDelivery = (deliveryId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('sub_action', 'edit_delivery')
    url.searchParams.set('delivery_id', deliveryId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditHomeworkDelivery = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('sub_action')
    url.searchParams.set('delivery_id', deliveryId.value as string)
    url.searchParams.set('sub_action', 'view_delivery')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const getOneHomeworkDelivery = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_delivery',
        delivery_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentDelivery.value = response.data
        loading.get = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const updateHomeworkDelivery = (args: Partial<HomeworkDelivery>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_delivery',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneHomeworkDelivery(deliveryId.value as string)
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.update = false
      })
  }

  return {
    deliveries: computed(() => deliveries),
    fetchHomeworkDeliveries,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentDeliveries: computed(() => currentDelivery),
    goToViewHomeworkDelivery,
    closeViewHomeworkDelivery,
    deliveryId: deliveryId,
    getOneHomeworkDelivery,
    showViewScreen: computed(() => showViewScreen),
    showEditScreen: computed(() => showEditScreen),
    updateHomeworkDelivery,
    goToEditHomeworkDelivery,
    closeEditHomeworkDelivery
  }
})
