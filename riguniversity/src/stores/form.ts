import { defineStore } from 'pinia'

export interface LinearScaleOptions {
  min: number
  max: number
  step: number
  labels: {
    [key: number]: string
  }
}
export interface TextOptions {
  add_word_count?: boolean
  min?: number
  max?: number
  regex?: string
}

export interface Option {
  label: string
  value: string
  points?: number
}

export type QuestionType = 'linear-scale' | 'radio' | 'text' | 'textarea' | 'checkbox' | 'dropdown'

export interface Question {
  question_id: string
  question: string
  type: QuestionType
  linear_scale_options?: LinearScaleOptions
  text_options: TextOptions
  options?: Option[]
  required?: boolean
}

export interface Form {
  title: string
  description: string
  questions: Question[]
}

export const useFormStore = defineStore('formStore', {
  state: (): { form: Form } => ({
    form: {
      title: '',
      description: '',
      questions: []
    }
  }),
  actions: {
    addQuestion(type: QuestionType) {
      const newQuestion: Question = {
        question_id: `q${this.form.questions.length + 1}`,
        question: '',
        type,
        options:
          type === 'radio' || type === 'checkbox' || type === 'dropdown'
            ? [{ label: '', value: '' }]
            : undefined,
        text_options:
          type === 'text' || type === 'textarea'
            ? { add_word_count: false, min: 250, max: 300 }
            : {},
        linear_scale_options:
          type === 'linear-scale'
            ? {
                min: 1,
                max: 10,
                step: 1,
                labels: {
                  1: 'Did not meet expectations',
                  10: 'Expert - demonstrates a deep understanding and mastery'
                }
              }
            : undefined
      }
      this.form.questions.push(newQuestion)
    },
    removeQuestion(index: number) {
      this.form.questions.splice(index, 1)
    },
    addOption(questionIndex: number) {
      const newOption = { label: '', value: '' }
      this.form.questions[questionIndex].options?.push(newOption)
    },
    removeOption(questionIndex: number, optionIndex: number) {
      this.form.questions[questionIndex].options?.splice(optionIndex, 1)
    },
    generateForm() {
      console.log(this.form)
      // Handle form submission or further processing
    }
  }
})
