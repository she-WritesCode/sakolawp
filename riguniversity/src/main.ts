import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import AdminApp from './admin-views/App.vue'
import PublicApp from './public-views/App.vue'

const adminApp = createApp(AdminApp)

adminApp.use(createPinia())

adminApp.mount('#skwp-admin-app')

const publicApp = createApp(PublicApp)

publicApp.use(createPinia())

publicApp.mount('#skwp-public-app')
