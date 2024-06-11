/**
 * Recursively builds URLSearchParams from a nested object.
 *
 * @param {Object} obj - The object to be converted to URLSearchParams.
 * @param {string} [prefix] - The prefix for nested keys (used internally).
 * @returns {URLSearchParams} - The resulting URLSearchParams object.
 */
function buildSearchParams(obj: any, prefix: string = ''): URLSearchParams {
  const params = new URLSearchParams()

  for (const key in obj) {
    // eslint-disable-next-line no-prototype-builtins
    if (obj.hasOwnProperty(key)) {
      const value = obj[key]
      const paramKey = prefix ? `${prefix}[${key}]` : key

      if (typeof value === 'object' && value !== null) {
        if (Array.isArray(value)) {
          value.forEach((item, index) => {
            if (typeof item === 'object' && item !== null) {
              // Recursively handle nested objects within arrays
              const nestedParams = buildSearchParams(item, `${paramKey}[${index}]`)
              nestedParams.forEach((val, nestedKey) => {
                params.append(nestedKey, val)
              })
            } else {
              params.append(`${paramKey}[${index}]`, item)
            }
          })
        } else {
          // Recursively handle nested objects
          const nestedParams = buildSearchParams(value, paramKey)
          nestedParams.forEach((val, nestedKey) => {
            params.append(nestedKey, val)
          })
        }
      } else {
        params.append(paramKey, value)
      }
    }
  }

  return params
}

/**
 * Converts a nested object to URLSearchParams.
 *
 * @param {Object} obj - The object to be converted.
 * @returns {URLSearchParams} - The resulting URLSearchParams object.
 */
export function convertObjectToSearchParams(obj: any): URLSearchParams {
  return buildSearchParams(obj)
}
