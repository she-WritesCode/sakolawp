import { defineStore } from 'pinia'

export interface LinearScaleOptions {
  min: number
  max: number
  step: number
  minLabel: string
  maxLabel: string
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

export type QuestionType =
  | 'linear-scale'
  | 'radio'
  | 'text'
  | 'textarea'
  | 'checkbox'
  | 'dropdown'
  | 'file'

export const QuestionLabels = {
  'linear-scale': 'Linear Scale',
  radio: 'Radio',
  text: 'Short Text',
  textarea: 'Long Text',
  checkbox: 'Checkbox',
  dropdown: 'Dropdown',
  file: 'File Upload'
}

export interface Question {
  question_id: string
  question: string
  type: QuestionType
  accepts?: string
  regex?: string
  multiple?: boolean
  linear_scale_options: LinearScaleOptions
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
        question: 'Your Submission',
        type,
        accepts: '*',
        required: true,
        options:
          type === 'radio' || type === 'checkbox' || type === 'dropdown'
            ? [{ label: '', value: '' }]
            : [],
        text_options:
          type === 'text' || type === 'textarea'
            ? { add_word_count: false, min: 250, max: 300, regex: '' }
            : { add_word_count: false, min: 0, max: 0, regex: '' },

        linear_scale_options: {
          min: 1,
          max: 10,
          step: 1,
          minLabel: 'Did not meet expectations',
          maxLabel: 'Expert - demonstrates a deep understanding and mastery'
        }
      }
      this.form.questions.push(newQuestion)
    },
    replaceQuestions(questions: Question[]) {
      this.form.questions = questions
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
    getFileTypeOptions() {
      return [
        { label: 'All Files', value: '*' },
        { label: 'Images', value: 'image/*' },
        { label: 'Word Documents', value: '.docs|.docx' },
        { label: 'PDF File', value: '.pdf' },
        { label: 'CSV File', value: '.csv' }
      ]
    },
    getTextTypeOptions() {
      return [
        { label: 'Plain', value: '' },
        { label: 'Url', value: '\b((https?|ftp)://[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|])' },
        { label: 'Email', value: '\b[A-Z0-9._%+-]+@[A-Z0-9.-]+.[A-Z]{2,}\b' }
      ]
    },
    generateForm() {
      console.log(this.form)
      // Handle form submission or further processing
    }
  }
})
