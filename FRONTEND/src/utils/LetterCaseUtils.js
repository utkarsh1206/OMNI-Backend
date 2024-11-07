export const toCamelCase = (str) => {
  return str
    .split('_') // Split by underscore
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()) // Capitalize the first letter of each word
    .join(' ') // Join with spaces
}
