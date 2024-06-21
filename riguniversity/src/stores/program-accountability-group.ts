import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface programAccountabilityGroup {
  accountability_id?: string
  section_id: string
  name: string
  class_id: string
  created_at: string
  updated_at: string
}

export const useprogramAccountabilityGroupStore = defineStore('programAccountabilityGroup', () => {
  const toast = useToast()
  const programAccountabilityGroups = ref<programAccountabilityGroup[]>([])
  const currentprogramAccountabilityGroup = ref<programAccountabilityGroup | undefined>(undefined)
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
  const programAccountabilityGroupId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('subject_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddAccountabilityGroupForm = computed(
    () => action.value === 'add_programAccountabilityGroup'
  )
  const showViewAccountabilityGroupScreen = computed(
    () => action.value === 'view_programAccountabilityGroup' && !!programAccountabilityGroupId.value
  )
  const showEditAccountabilityGroupScreen = computed(
    () => action.value === 'add_programAccountabilityGroup' && !!programAccountabilityGroupId.value
  )

  watch(filter, () => {
    fetchprogramAccountabilityGroups()
  })

  const fetchprogramAccountabilityGroups = () => {
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
        programAccountabilityGroups.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewprogramAccountabilityGroup = (programAccountabilityGroupId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_programAccountabilityGroup')
    url.searchParams.set('class_id', programAccountabilityGroupId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewprogramAccountabilityGroup = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('class_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditprogramAccountabilityGroup = (programAccountabilityGroupId: string) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programAccountabilityGroup')
    url.searchParams.set('class_id', programAccountabilityGroupId)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditprogramAccountabilityGroup = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.set('class_id', programAccountabilityGroupId.value as string)
    url.searchParams.set('action', 'view_programAccountabilityGroup')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddAccountabilityGroupForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programAccountabilityGroup')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddAccountabilityGroupForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneprogramAccountabilityGroup = (id: string) => {
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
        currentprogramAccountabilityGroup.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createprogramAccountabilityGroup = (args: Partial<programAccountabilityGroup>) => {
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
        currentprogramAccountabilityGroup.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programAccountabilityGroup created successfully',
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

  const updateprogramAccountabilityGroup = (args: Partial<programAccountabilityGroup>) => {
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
        getOneprogramAccountabilityGroup(programAccountabilityGroupId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programAccountabilityGroup updated successfully',
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

  const deleteprogramAccountabilityGroup = (id: string) => {
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
        fetchprogramAccountabilityGroups()
      })
  }

  return {
    programAccountabilityGroups: computed(() => programAccountabilityGroups),
    fetchprogramAccountabilityGroups,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentprogramAccountabilityGroup: computed(() => currentprogramAccountabilityGroup),
    goToViewprogramAccountabilityGroup,
    closeViewprogramAccountabilityGroup,
    programAccountabilityGroupId,
    getOneprogramAccountabilityGroup,
    showAddAccountabilityGroupForm,
    showViewAccountabilityGroupScreen,
    showEditAccountabilityGroupScreen,
    goToAddAccountabilityGroupForm,
    closeAddAccountabilityGroupForm,
    createprogramAccountabilityGroup,
    updateprogramAccountabilityGroup,
    deleteprogramAccountabilityGroup,
    goToEditprogramAccountabilityGroup,
    closeEditprogramAccountabilityGroup
  }
})
