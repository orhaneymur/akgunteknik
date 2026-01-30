import axios from 'axios';
import router from '../router.js';

// Create axios instance with base configuration
const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Request interceptor - Add auth token
apiClient.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor - Handle global errors
apiClient.interceptors.response.use(
    (response) => {
        // Backend returns { success, data, message, errors }
        // If success is false, treat as error
        if (response.data && response.data.success === false) {
            return Promise.reject({
                response: {
                    data: response.data,
                    status: response.status,
                },
            });
        }
        return response;
    },
    (error) => {
        // Handle 401 Unauthorized - Token expired or invalid
        if (error.response?.status === 401) {
            // Clear auth data
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            
            // Redirect to login using router if available, otherwise use window.location
            if (router && router.currentRoute.value.name !== 'Login') {
                router.push({ name: 'Login' });
            } else if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }

        // Handle 403 Forbidden - Insufficient permissions
        if (error.response?.status === 403) {
            console.error('Access forbidden:', error.response?.data?.message || 'Insufficient permissions');
        }

        // Handle 422 Validation errors - Already formatted by backend
        if (error.response?.status === 422) {
            // Validation errors are already in the response.data.errors format
            return Promise.reject(error);
        }

        // Handle 500 Server errors
        if (error.response?.status >= 500) {
            console.error('Server error:', error.response?.data?.message || 'Internal server error');
        }

        return Promise.reject(error);
    }
);

export default apiClient;
