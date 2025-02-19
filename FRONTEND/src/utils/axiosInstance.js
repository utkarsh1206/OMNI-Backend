import axios from 'axios';
import { BASE_URL } from '../config';

// Create an Axios instance
const axiosInstance = axios.create({
    baseURL: `${BASE_URL}/api`,
    headers: {
        'Content-Type': 'application/json'
    }
});

// Add request interceptor to handle loading state or modify headers
axiosInstance.interceptors.request.use(
    (config) => {
        config.headers['Authorization'] = `Bearer ${localStorage.getItem('token')}`;

        // You can also manage a global loading state here, e.g., using Redux or Context API
        // For example, dispatching a loading action if you're using Redux
        // store.dispatch({ type: 'SHOW_LOADING' });

        return config;
    },
    (error) => Promise.reject(error)
);

// Add response interceptor to handle errors or response manipulation
axiosInstance.interceptors.response.use(
    (response) => {
        // Hide loader here
        return response;
    },
    (error) => {
        if (error?.response?.status === 401) {
            window.location.href = '/login';
        }
        // Hide loader if there's an error
        return Promise.reject(error);
    }
);

export default axiosInstance;