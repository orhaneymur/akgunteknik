import apiClient from './client.js';

export const authApi = {
    async login(credentials) {
        const response = await apiClient.post('/core/login', credentials);
        return response.data;
    },

    async logout() {
        try {
            await apiClient.post('/core/logout');
        } catch (error) {
            // Even if logout fails on server, clear local storage
            console.warn('Logout request failed, clearing local storage anyway');
        } finally {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
        }
    },

    getCurrentUser() {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    },

    isAuthenticated() {
        return !!localStorage.getItem('token');
    },
};
