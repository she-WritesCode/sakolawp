import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'
import type { programAccountabilityGroup } from './program-accountability-group'

export interface programParentGroup {
  section_id?: string
  name: string
  class_id: string
  teacher_id: string
  created_at: string
  updated_at: string
  accountability_count: string
  accountabilities: programAccountabilityGroup[]
}

export const useprogramParentGroupStore = defineStore('programParentGroup', () => {
  const toast = useToast()
  const programParentGroups = ref<programParentGroup[]>([])
  const currentprogramParentGroup = ref<programParentGroup | undefined>(undefined)
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
  const programParentGroupId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddParentGroupForm = computed(() => action.value === 'add_programParentGroup')
  const showViewParentGroupScreen = computed(
    () => action.value === 'view_programParentGroup' && !!programParentGroupId.value
  )
  const showEditParentGroupScreen = computed(
    () => action.value === 'add_programParentGroup' && !!programParentGroupId.value
  )

  watch(filter, () => {
    fetchprogramParentGroups()
  })

  const fetchprogramParentGroups = () => {
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
        programParentGroups.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewprogramParentGroup = (programParentGroupId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_programParentGroup')
    url.searchParams.set('class_id', programParentGroupId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewprogramParentGroup = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditprogramParentGroup = (programParentGroupId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programParentGroup')
    url.searchParams.set('class_id', programParentGroupId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditprogramParentGroup = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', programParentGroupId.value as string)
    url.searchParams.set('action', 'view_programParentGroup')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddParentGroupForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programParentGroup')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddParentGroupForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneprogramParentGroup = (id: string) => {
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
        currentprogramParentGroup.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createprogramParentGroup = (args: Partial<programParentGroup>) => {
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
        currentprogramParentGroup.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programParentGroup created successfully',
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

  const updateprogramParentGroup = (args: Partial<programParentGroup>) => {
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
        getOneprogramParentGroup(programParentGroupId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programParentGroup updated successfully',
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

  const deleteprogramParentGroup = (id: string) => {
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
        fetchprogramParentGroups()
      })
  }

  return {
    programParentGroups: computed(() => programParentGroups),
    fetchprogramParentGroups,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentprogramParentGroup: computed(() => currentprogramParentGroup),
    goToViewprogramParentGroup,
    closeViewprogramParentGroup,
    programParentGroupId,
    getOneprogramParentGroup,
    showAddParentGroupForm,
    showViewParentGroupScreen,
    showEditParentGroupScreen,
    goToAddParentGroupForm,
    closeAddParentGroupForm,
    createprogramParentGroup,
    updateprogramParentGroup,
    deleteprogramParentGroup,
    goToEditprogramParentGroup,
    closeEditprogramParentGroup
  }
})
