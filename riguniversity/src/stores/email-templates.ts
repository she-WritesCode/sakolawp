import { ref, computed, reactive } from 'vue'
import { defineStore } from 'pinia'
import { convertObjectToSearchParams } from '@/utils/search'
import { useToast } from 'primevue/usetoast'

export interface Template {
  id: string
  title: string
  content: {
    subject: string
    template: string
  }
}

export const useEmailTemplateStore = defineStore('emailTemplate', () => {
  const toast = useToast()
  const loading = reactive({ list: false, update: false })
  const templates = ref<Template[]>([])
  const placeholders = ref<string[]>([])
  const errors = reactive<Record<string, any>>({})

  const fetchTemplates = async () => {
    loading.list = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'run_fetch_email_templates'
      })
    })
      .then((response) => response.json())
      .then((response) => {
        templates.value = response.data.templates
        placeholders.value = response.data.placeholders
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.list = false
      })
  }

  const saveTemplates = async () => {
    if (!templates.value.every(validateTemplate)) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Please fill all required fields',
        life: 3000
      })
      return
    }

    // @ts-ignore
    loading.update = true
    // @ts-ignore
    fetch(skwp_ajax_object.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: convertObjectToSearchParams({
        action: 'run_save_email_templates',
        templates: templates.value
      })
    })
      .then((response) => response.json())
      .then(() => {
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Email templates saved successfully',
          life: 3000
        })
        fetchTemplates()
      })
      .catch((error) => {
        console.error('Error:', error)
      })
      .finally(() => {
        loading.update = false
      })
  }

  const validateTemplate = (template: Template) => {
    errors[template.id] = {}
    if (!template.content.subject || !template.content.template) {
      if (!template.content.subject) errors[template.id]['subject'] = 'Email subject is required'
      if (!template.content.template) errors[template.id]['template'] = 'Email template is required'

      return false
    }
    return true
  }

  return {
    loading,
    templates: computed(() => templates),
    placeholders: computed(() => placeholders),
    errors: computed(() => errors),
    saveTemplates,
    fetchTemplates
  }
})
