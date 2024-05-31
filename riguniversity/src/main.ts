import 'primeicons/primeicons.css'
import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
// @ts-ignore
import Rig from '@/presets/rig' //import preset

// Define the type for the imported modules
type ImportedModule = Record<string, { default: any }>

// Dynamically import all components from the public-views folder
const publicViews: ImportedModule = import.meta.glob('./public-views/*.vue', { eager: true })
// Dynamically import all components from the admin-views folder
const adminViews: ImportedModule = import.meta.glob('./admin-views/*.vue', { eager: true })

// Mount each public view component
Object.entries(publicViews).forEach(([path, module]) => {
  const componentName = path
    .split('/')
    .pop()
    ?.replace(/\.\w+$/, '')

  const mountElement = document.getElementById(`run-${componentName?.toLowerCase()}`)
  if (mountElement) {
    createApp(module.default)
      .use(createPinia())
      .use(PrimeVue, { unstyled: true, pt: Rig })
      .mount(`#run-${componentName?.toLowerCase()}`)
  }
})

// Mount each admin view component
Object.entries(adminViews).forEach(([path, module]) => {
  const componentName = path
    .split('/')
    .pop()
    ?.replace(/\.\w+$/, '')
  const mountElement = document.getElementById(`run-${componentName?.toLowerCase()}`)
  if (mountElement) {
    createApp(module.default)
      .use(createPinia())
      .use(PrimeVue, { unstyled: true, pt: Rig })
      .mount(`#run-${componentName?.toLowerCase()}`)
  }
})
