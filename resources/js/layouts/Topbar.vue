<template>
    <div class="relative z-10 flex-shrink-0 flex h-16 bg-white/80 backdrop-blur-xl border-b border-gray-200/50 shadow-sm">
        <button 
            @click="$emit('toggle-sidebar')"
            class="px-4 border-r border-gray-200/50 text-gray-600 hover:text-gray-900 hover:bg-gray-50/50 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden">
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
        </button>
        <div class="flex-1 px-6 flex justify-between items-center">
            <div class="flex-1 flex items-center">
                <h1 class="text-lg font-semibold text-gray-900 hidden sm:block">
                    {{ pageTitle }}
                </h1>
            </div>
            <div class="ml-4 flex items-center space-x-4">
                <!-- Notifications -->
                <button class="relative p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100/50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">View notifications</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                </button>

                <!-- User Profile -->
                <div class="flex items-center space-x-3">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-medium text-gray-900">{{ currentUser?.name || 'Kullanıcı' }}</p>
                        <p class="text-xs text-gray-500">{{ currentUser?.role ? currentUser.role.charAt(0).toUpperCase() + currentUser.role.slice(1) : '' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg ring-2 ring-white">
                        <span class="text-white font-semibold text-sm">{{ userInitial }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { authApi } from '../api/auth.js';

export default {
    name: 'Topbar',
    data() {
        return {
            currentUser: null,
            pageTitle: 'Dashboard'
        };
    },
    mounted() {
        this.currentUser = authApi.getCurrentUser();
        this.updatePageTitle();
        this.$watch(() => this.$route.path, () => {
            this.updatePageTitle();
        });
    },
    computed: {
        userInitial() {
            return this.currentUser?.name ? this.currentUser.name.charAt(0).toUpperCase() : 'U';
        }
    },
    methods: {
        updatePageTitle() {
            const route = this.$route;
            const titles = {
                '/dashboard': 'Dashboard',
                '/products': 'Ürünler',
                '/sales': 'Satışlar',
                '/customers': 'Müşteriler',
                '/invoices': 'Faturalar',
                '/users': 'Kullanıcılar',
                '/product-categories': 'Kategoriler',
                '/brands': 'Markalar',
                '/product-models': 'Modeller'
            };
            this.pageTitle = titles[route.path] || route.meta?.title || 'Panel';
        },
        async handleLogout() {
            if (confirm('Çıkış yapmak istediğinize emin misiniz?')) {
                await authApi.logout();
                this.$router.push('/login');
            }
        },
    },
};
</script>
