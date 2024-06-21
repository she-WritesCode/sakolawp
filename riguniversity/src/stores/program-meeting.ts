import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import * as yup from 'yup'
import { useToast } from 'primevue/usetoast'
import { convertObjectToSearchParams } from '@/utils/search'

export interface CalendarDay {
  id: string
  dayIndex: number
  day: number
  dayFromEnd: number
  weekday: number
  weekdayOrdinal: number
  weekdayOrdinalFromEnd: number
  week: number
  weekFromEnd: number
  weeknumber: number
  month: number
  year: number
  date: Date
  position: number
  label: string
  ariaLabel: string
  weekdayPosition: number
  weekdayPositionFromEnd: number
  weekPosition: number
  isoWeeknumber: number
  startDate: Date
  noonDate: Date
  endDate: Date
  isToday: boolean
  isFirstDay: boolean
  isLastDay: boolean
  isDisabled: boolean
  isFocusable: boolean
  inMonth: boolean
  inPrevMonth: boolean
  inNextMonth: boolean
  onTop: boolean
  onBottom: boolean
  onLeft: boolean
  onRight: boolean
  classes: Array<string | Object>
  locale: Locale
}

export interface programMeeting {
  ID?: number
  title?: string
  content?: string
  excerpt?: string
  permalink?: string
  date?: string
  author?: string
  meta?: Record<string, string[]>
}
export interface CreateprogramMeeting {
  title: string
  content?: string
  meta: {
    _sakolawp_event_location?: string
    _sakolawp_event_date: string
    _sakolawp_event_date_clock: string
    _sakolawp_event_class_id: string
  }
}
export const createprogramMeetingSchema = yup.object({
  title: yup.string().required(),
  content: yup.string().optional(),
  meta: yup.object({
    _sakolawp_event_date: yup.string().required().label('Event date'),
    _sakolawp_event_date_clock: yup.string().required().label('Event time'),
    _sakolawp_event_class_id: yup.string().required().label('Event program'),
    _sakolawp_event_location: yup.string().optional().label('Event location')
  })
})

const programMeetingStore = () => {
  const toast = useToast()
  const programMeetings = ref<programMeeting[]>([])
  const currentprogramMeeting = ref<programMeeting | undefined>(undefined)
  const filter = reactive<{
    search: string
    class_id: string
    meta_query: { key: string; value: string; compare: string }[]
  }>({
    search: '',
    class_id: '',
    meta_query: []
  })
  const loading = reactive({
    list: false,
    get: false,
    create: false,
    update: false,
    delete: false
  })
  const programMeetingId = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('event_id')
  })
  const action = computed(() => {
    const url = new URL(window.location.href)
    return url.searchParams.get('action')
  })

  const showAddForm = computed(() => action.value === 'add_programMeeting')
  const showViewScreen = computed(
    () => action.value === 'view_programMeeting' && !!programMeetingId.value
  )
  const showEditScreen = computed(
    () => action.value === 'add_programMeeting' && !!programMeetingId.value
  )

  watch(filter, () => {
    fetchprogramMeetings()
  })

  const fetchprogramMeetings = () => {
    if (!filter.search) loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_list_events',
        ...filter
      })
    })
      .then((response) => response.json())
      .then((response) => {
        programMeetings.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const goToViewprogramMeeting = (programMeetingId: number) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'view_programMeeting')
    url.searchParams.set('event_id', `${programMeetingId}`)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeViewprogramMeeting = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('event_id')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }
  const goToEditprogramMeeting = (programMeetingId: number) => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programMeeting')
    url.searchParams.set('event_id', `${programMeetingId}`)
    // showViewScreen.value = true
    window.location.href = url.toString()
  }

  const closeEditprogramMeeting = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    url.searchParams.delete('event_id')
    // We don't need to go back to view screen since we don't have one
    // url.searchParams.set('event_id', programMeetingId.value as string)
    // url.searchParams.set('action', 'view_programMeeting')
    // showViewScreen.value = false
    window.location.href = url.toString()
  }

  const goToAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.set('action', 'add_programMeeting')
    // showAddForm.value = true
    window.location.href = url.toString()
  }

  const closeAddForm = () => {
    const url = new URL(window.location.href)
    url.searchParams.delete('action')
    // showAddForm.value = false
    window.location.href = url.toString()
  }

  const getOneprogramMeeting = (id: string) => {
    loading.get = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_single_event',
        event_id: id
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentprogramMeeting.value = response.data
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.get = false
      })
  }

  const createprogramMeeting = (args: CreateprogramMeeting) => {
    loading.create = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_create_event',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then((response) => {
        currentprogramMeeting.value = response.data
        // showAddForm.value = false
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programMeeting created successfully',
          life: 3000
        })
      })
      .catch((error) => {
        console.error('Error:', error)
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 3000 })
      })
      .finally(() => {
        closeAddForm()
        loading.create = false
      })
  }

  const updateprogramMeeting = (args: Partial<CreateprogramMeeting> & { ID: number }) => {
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_update_event',
        ...(args as any)
      })
    })
      .then((response) => response.json())
      .then(() => {
        getOneprogramMeeting(programMeetingId.value as string)
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'programMeeting updated successfully',
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

  const deleteprogramMeeting = (id: string) => {
    loading.delete = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_delete_event',
        event_id: id
      })
    })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.delete = false
        fetchprogramMeetings()
      })
  }

  async function generateQRCode(skwpEventId: number, print = true) {
    try {
      // @ts-ignore
      const response = await fetch(skwp_ajax_object.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'sakolawp_generate_qr_code',
          event_id: `${skwpEventId}`
        })
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (print && data.data.image) printImage(data.data.image)

      return data.data.image
    } catch (error) {
      console.error('Error generating QR code:', error)
    }
  }
  function printImage(imageUrl: string) {
    const printWindow = window.open('', '_blank')
    printWindow?.document.write(
      [
        '<html>',
        '   <head>',
        '   </head>',
        '   <body onload="window.print()" onafterprint="window.close()">',
        '       <img style="width:100%" src="' + imageUrl + '"/>',
        '   </body>',
        '</html>'
      ].join('')
    )
    printWindow?.document.close()
    // wait 2 seconds
    setTimeout(() => {
      printWindow?.print()
    }, 2000)
  }

  return {
    programMeetings: computed(() => programMeetings),
    fetchprogramMeetings,
    filter: computed(() => filter),
    loading: computed(() => loading),
    currentprogramMeeting: computed(() => currentprogramMeeting),
    goToViewprogramMeeting,
    closeViewprogramMeeting,
    programMeetingId,
    getOneprogramMeeting,
    showAddForm,
    showViewScreen,
    showEditScreen,
    goToAddForm,
    closeAddForm,
    createprogramMeeting,
    updateprogramMeeting,
    deleteprogramMeeting,
    goToEditprogramMeeting,
    closeEditprogramMeeting,
    generateQRCode
  }
}

export const useprogramMeetingStore = (uniqueStoreName = 'programMeeting') =>
  defineStore(uniqueStoreName, programMeetingStore)()
