//  * Date helper functions
//  * @category Helper

import * as dateFns from 'date-fns'

/**
 * Date helper functions
 * @category Helper
 */
export class DateHelper {
  /**
   * Format a date to a readable string
   * @param date - Date object or date string
   * @param format - Format string using date-fns
   * @returns Formatted date string
   */
  static formatDate(date: Date | string, format: string = 'MMMM dd, yyyy'): string {
    try {
      const dateObj = typeof date === 'string' ? new Date(date) : date
      return dateFns.format(dateObj, format)
    } catch (error) {
      console.error(error)
      return ''
    }
  }

  /**
   * Format a time to a readable string
   * @param time - Date object or time string
   * @param format - Format string using date-fns
   * @returns Formatted time string
   */
  static formatTime(time: Date | string, format: string = 'h:mm aa'): string {
    try {
      const timeObj = typeof time === 'string' ? new Date(time) : time
      return dateFns.format(timeObj, format)
    } catch (error) {
      console.error(error)
      return ''
    }
  }

  /**
   * Get relative time from now
   * @param dateTime - Date object or date-time string
   * @returns Relative time string
   */
  static relativeTime(dateTime: Date | string): string {
    const dateTimeObj = typeof dateTime === 'string' ? new Date(dateTime) : dateTime
    const diff = Date.now() - dateTimeObj.getTime()

    if (diff < 60 * 1000) {
      return 'just now'
    } else if (diff < 60 * 60 * 1000) {
      return `${Math.floor(diff / (60 * 1000))} minutes ago`
    } else if (diff < 24 * 60 * 60 * 1000) {
      return `${Math.floor(diff / (60 * 60 * 1000))} hours ago`
    } else if (diff < 7 * 24 * 60 * 60 * 1000) {
      return `${Math.floor(diff / (24 * 60 * 60 * 1000))} days ago`
    } else {
      return dateFns.format(dateTimeObj, 'MMMM dd, yyyy')
    }
  }

  /**
   * Get start of the day timestamp
   * @param date - Date object or date string
   * @returns Start of the day timestamp
   */
  static startOfDay(date: Date | string): Date {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return dateFns.startOfDay(dateObj)
  }

  /**
   * Get end of the day timestamp
   * @param date - Date object or date string
   * @returns End of the day timestamp
   */
  static endOfDay(date: Date | string): Date {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return dateFns.endOfDay(dateObj)
  }

  /**
   * Add days to a date
   * @param date - Date object or date string
   * @param days - Number of days to add
   * @returns New date with days added
   */
  static addDays(date: Date | string, days: number): Date {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return dateFns.addDays(dateObj, Number(days))
  }

  /**
   * Subtract days from a date
   * @param date - Date object or date string
   * @param days - Number of days to subtract
   * @returns New date with days subtracted
   */
  static subtractDays(date: Date | string, days: number): Date {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return dateFns.subDays(dateObj, days)
  }

  static toISOString(date: Date | string): string {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return dateFns.format(dateObj, 'yyyy-MM-ddTHH:mm:ssZ')
  }

  static toSimpleBackendDateString(date: Date | string): string {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return dateFns.format(dateObj, 'yyyy-MM-dd')
  }

  static toSimpleBackendTimeString(date: Date | string): string {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return dateFns.format(dateObj, 'HH:mm')
  }
}
