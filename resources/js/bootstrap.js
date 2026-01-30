import axios from 'axios';
// Keep axios available globally for backward compatibility
window.axios = axios;

// Import API client for new code to use
import apiClient from './api/client.js';
window.apiClient = apiClient;

