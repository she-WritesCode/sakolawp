import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'
import type { CohortAccountabilityGroup } from './cohort-accountability-group'

export interface CohortParentGroup {
  section_id?: string
  name: string
  class_id: string
  teacher_id: string
  created_at: string
  updated_at: string
  accountability_count: string
  accountabilities: CohortAccountabilityGroup[]
}

export const useCohortParentGroupStore = defineStore('cohortParentGroup', () => {
  const toast = useToast()
  const cohortParentGroups = ref<CohortParentGroup[]>([])
  const currentCohortParentGroup = ref<CohortParentGroup | undefined>(undefined)
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
  const cohortParentGroupId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddParentGroupForm = computed(() => action.value === 'add_cohortParentGroup')
  const showViewParentGroupScreen = computed(
    () => action.value === 'view_cohortParentGroup' && !!cohortParentGroupId.value
  )
  const showEditParentGroupScreen = computed(
    () => action.value === 'add_cohortParentGroup' && !!cohortParentGroupId.value
  )

  watch(filter, () => {
    fetchCohortParentGroups()
  })

  const fetchCohortParentGroups = () => {
    if (!filter.search) loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_list_sections',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        cohortParentGroups.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewCohortParentGroup = (cohortParentGroupId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_cohortParentGroup')
    url.searchParams.set('class_id', cohortParentGroupId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewCohortParentGroup = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditCohortParentGroup = (cohortParentGroupId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortParentGroup')
    url.searchParams.set('class_id', cohortParentGroupId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditCohortParentGroup = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', cohortParentGroupId.value as string)
    url.searchParams.set('action', 'view_cohortParentGroup')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddParentGroupForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_cohortParentGroup')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddParentGroupForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneCohortParentGroup = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_section',
        class_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohortParentGroup.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createCohortParentGroup = (args: Partial<CohortParentGroup>) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_create_section',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentCohortParentGroup.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortParentGroup created successfully',
          life: 3000
        })
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        closeAddParentGroupForm()
        loading.create = false
      })
  }

  const updateCohortParentGroup = (args: Partial<CohortParentGroup>) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_section',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneCohortParentGroup(cohortParentGroupId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'CohortParentGroup updated successfully',
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

  const deleteCohortParentGroup = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_section',
        class_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchCohortParentGroups()
      })
  }

  return {
    cohortParentGroups: computed(() => cohortParentGroups),
    fetchCohortParentGroups,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentCohortParentGroup: computed(() => currentCohortParentGroup),
    goToViewCohortParentGroup,
    closeViewCohortParentGroup,
    cohortParentGroupId,
    getOneCohortParentGroup,
    showAddParentGroupForm,
    showViewParentGroupScreen,
    showEditParentGroupScreen,
    goToAddParentGroupForm,
    closeAddParentGroupForm,
    createCohortParentGroup,
    updateCohortParentGroup,
    deleteCohortParentGroup,
    goToEditCohortParentGroup,
    closeEditCohortParentGroup
  }
})
