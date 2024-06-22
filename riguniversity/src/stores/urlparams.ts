// stores/urlParams.js
import { defineStore } from 'pinia'
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'

export const useUrlParamsStore = defineStore('urlParams', () => {
  const urlParams = ref(new URL(window.location.href).searchParams)

  const updateUrlParams: () => void = () => {
    console.log('updateUrlParams')
    urlParams.value = new URL(window.location.href).searchParams
  }

  onMounted(() => {
    window.addEventListener('popstate', updateUrlParams)
    window.addEventListener('pushstate', updateUrlParams)
    window.addEventListener('replacestate', updateUrlParams)
  })

  watch(() => window.location.href, updateUrlParams)

  onUnmounted(() => {
    window.removeEventListener('popstate', updateUrlParams)
    window.removeEventListener('pushstate', updateUrlParams)
    window.removeEventListener('replacestate', updateUrlParams)
  })

  return { urlParams: computed(() => urlParams) }
})
