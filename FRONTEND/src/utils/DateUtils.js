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
  
  return new Date(string).toLocaleDateString([], options) ;
}
