import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'

export interface UserResponse {
  data: User
}
export interface User {
  display_name: string
  ID: number
}

export const useUserStore = defineStore('user', () => {
  const users = ref<UserResponse[]>([])
  const currentUser = ref<User | null>(null)
  const filter = reactive({
    search: '',
    role: ''
  })
  const loading = ref(false)
  const userId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('user_id')
  })

  watch(filter, () => {
    fetchUsers()
  })

  const fetchUsers = () => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_users',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        users.value = response.data
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  const goToViewUser = (userId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('user_id', userId)
    window.location.href = url.toString()
  }

  const getOneUser = (id: string) => {
    loading.value = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_user',
        user_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentUser.value = response.data
        loading.value = false
      })
      .catch((error) => {
        console.error('Error:', error)
      })
  }

  return {
    users: computed(() => users),
    fetchUsers,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentUser: computed(() => currentUser),
    goToViewUser,
    userId,
    getOneUser
  }
})
