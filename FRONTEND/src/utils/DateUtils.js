// October 29, 2024
export const formatDate = (string) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(string).toLocaleDateString([], options)
}

// Oct 29, 2024, 05:23 PM
export const formatDateWithTime = (string) => {
  const options = {
    year: 'numeric',
    month: 'short',  // For abbreviated month (e.g., Jan, Feb)
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true  // To use 12-hour format (AM/PM)
  };

  return new Date(string).toLocaleDateString([], options);
}

export const extractTime = (date) => {
  if (!date) {
    return '00:00:00';
  }
  const hours = date.getHours().toString().padStart(2, '0');
  const minutes = date.getMinutes().toString().padStart(2, '0');
  const seconds = date.getSeconds().toString().padStart(2, '0');

  return `${hours}:${minutes}:${seconds}`;
}