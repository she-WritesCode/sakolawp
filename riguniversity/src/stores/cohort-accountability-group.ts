import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface CohortAccountabilityGroup {
  accountability_id?: string
  section_id: string
  name: string
  class_id: string
  created_at: string
  updated_at: string
}

export const useCohortAccountabilityGroupStore = defineStore('cohortAccountabilityGroup', () => {
  const toast = useToast()
  const cohortAccountabilityGroups = ref<CohortAccountabilityGroup[]>([])
  const currentCohortAccountabilityGroup = ref<CohortAccountabilityGroup | undefined>(undefined)
  const filter = reactive({
    search: '',
    class_id: ''
  })
  const loading = reactive({
    list: false,
    get: false,
    create: false,
    update: false,
    delete: false
  })
  const cohortAccountabilityGroupId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddAccountabilityGroupForm = computed(
    () => action.value === 'add_cohortAccountabilityGroup'
  )
  const showViewAccountabilityGroupScreen = computed(
    () => action.value === 'view_cohortAccountabilityGroup' && !!cohortAccountabilityGroupId.value
  )
  const showEditAccountabilityGroupScreen = computed(
    () => action.value === 'add_cohortAccountabilityGroup' && !!cohortAccountabilityGroupId.value
  )

  watch(filter, () => {
    fetchCohortAccountabilityGroups()
  })

  const fetchCohortAccountabilityGroups = () => {
    if (!filter.search) loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_accountabilities',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        cohortAccountabilityGroups.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewCohortAccountabilityGroup = (cohortAccountabilityGroupId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_cohortAccountabilityGroup')
    url.searchParams.set('class_id', cohortAccountabilityGroupId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewCohortAccountabilityGroup = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditCohortAccountabilityGroup = (cohortAccountabilityGroupId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortAccountabilityGroup')
    url.searchParams.set('class_id', cohortAccountabilityGroupId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditCohortAccountabilityGroup = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', cohortAccountabilityGroupId.value as string)
    url.searchParams.set('action', 'view_cohortAccountabilityGroup')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddAccountabilityGroupForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortAccountabilityGroup')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddAccountabilityGroupForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneCohortAccountabilityGroup = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_accountability',
        class_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohortAccountabilityGroup.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createCohortAccountabilityGroup = (args: Partial<CohortAccountabilityGroup>) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_create_accountability',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohortAccountabilityGroup.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortAccountabilityGroup created successfully',
          life: 3000
        })
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        closeAddAccountabilityGroupForm()
        loading.create = false
      })
  }

  const updateCohortAccountabilityGroup = (args: Partial<CohortAccountabilityGroup>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_accountability',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneCohortAccountabilityGroup(cohortAccountabilityGroupId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortAccountabilityGroup updated successfully',
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

  const deleteCohortAccountabilityGroup = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_accountability',
        class_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchCohortAccountabilityGroups()
      })
  }

  return {
    cohortAccountabilityGroups: computed(() => cohortAccountabilityGroups),
    fetchCohortAccountabilityGroups,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentCohortAccountabilityGroup: computed(() => currentCohortAccountabilityGroup),
    goToViewCohortAccountabilityGroup,
    closeViewCohortAccountabilityGroup,
    cohortAccountabilityGroupId,
    getOneCohortAccountabilityGroup,
    showAddAccountabilityGroupForm,
    showViewAccountabilityGroupScreen,
    showEditAccountabilityGroupScreen,
    goToAddAccountabilityGroupForm,
    closeAddAccountabilityGroupForm,
    createCohortAccountabilityGroup,
    updateCohortAccountabilityGroup,
    deleteCohortAccountabilityGroup,
    goToEditCohortAccountabilityGroup,
    closeEditCohortAccountabilityGroup
  }
})
