<template>
    <div class="flex flex-col flex-shrink-0 w-64 text-white bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 border-r border-slate-700/50 backdrop-blur-xl transition-all duration-300 shadow-2xl" :class="{ '-ml-64': !isOpen && isMobile }">
        <!-- Logo Area -->
        <div class="flex items-center justify-center h-20 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 shadow-lg border-b border-indigo-500/20">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <span class="text-lg font-bold tracking-tight text-white">{{ tenantName || 'Akgün Teknik' }}</span>
            </div>
        </div>

        <!-- User Profile (Mobile Only - Optional, skipping for now to keep clean) -->

        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: rgb(51 65 85) transparent;">
            <nav class="px-3 py-4 space-y-1">
                
                <!-- Group: Finans -->
                <div class="mt-4 mb-3 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Finans
                </div>
                <router-link to="/finance/expenses" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/finance/expenses') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.CurrencyDollarIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/finance/expenses') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Giderler
                </router-link>
                <router-link to="/finance/safes" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/finance/safes') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.BanknotesIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/finance/safes') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Kasa & Banka
                </router-link>
                <router-link to="/finance/categories" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/finance/categories') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.TagIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/finance/categories') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Gider Kategorileri
                </router-link>

                <!-- Group: Genel Bakış -->
                <div class="mt-6 mb-3 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Genel Bakış
                </div>
                <router-link to="/dashboard" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/dashboard') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.HomeIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/dashboard') ? 'text-white' : 'text-indigo-400 group-hover:text-indigo-300'" />
                    Dashboard
                </router-link>
                <router-link to="/reports" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/reports') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.ChartBarIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/reports') ? 'text-white' : 'text-indigo-400 group-hover:text-indigo-300'" />
                    Raporlar
                </router-link>

                <!-- Group: Stok Yönetimi -->
                <div class="mt-6 mb-3 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Stok Yönetimi
                </div>
                <router-link to="/products" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/products') && !isActive('/products/movements') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.CubeIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/products') && !isActive('/products/movements') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Ürün Listesi
                </router-link>
                <router-link to="/products/movements" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/products/movements') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.ArrowsRightLeftIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/products/movements') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Stok Hareketleri
                </router-link>
                 <router-link to="/purchase-orders" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/purchase-orders') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.ClipboardDocumentCheckIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/purchase-orders') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Alış Siparişleri
                </router-link>
                <router-link to="/suppliers" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/suppliers') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.TruckIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/suppliers') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Tedarikçiler
                </router-link>
                <router-link to="/transfers" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/transfers') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.ArrowPathRoundedSquareIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/transfers') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Depo Transferi
                </router-link>
                <router-link to="/returns" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/returns') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.ArrowUturnLeftIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/returns') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    İade & Geri Alım
                </router-link>
                
                <!-- Ürün Yönetimi Alt Menüsü -->
                <div class="mt-6 mb-3 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Ürün Yönetimi
                </div>
                <router-link to="/product-categories" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/product-categories') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.TagIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/product-categories') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Kategoriler
                </router-link>
                <router-link to="/brands" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/brands') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.CubeIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/brands') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Markalar
                </router-link>
                <router-link to="/product-models" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/product-models') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.CubeIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/product-models') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Modeller
                </router-link>

                <!-- Group: Satış & Müşteri -->
                <div class="mt-6 mb-3 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Satış & Müşteri
                </div>
                <router-link to="/sales/create" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/sales/create') ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white shadow-lg shadow-green-500/50' : 'text-slate-300'">
                    <component :is="icons.ShoppingCartIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/sales/create') ? 'text-white' : 'text-green-400 group-hover:text-green-300'" />
                    Hızlı Satış Yap
                </router-link>
                <router-link to="/sales" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/sales') && !isActive('/sales/create') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.BanknotesIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/sales') && !isActive('/sales/create') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Satış Listesi
                </router-link>
                <router-link to="/customers" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/customers') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.UsersIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/customers') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Müşteriler
                </router-link>
                <router-link to="/quotes" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/quotes') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.DocumentMagnifyingGlassIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/quotes') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Teklifler
                </router-link>

                <!-- Group: Finans -->
                <div class="mt-6 mb-3 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Finans
                </div>
                <router-link to="/invoices" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/invoices') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.DocumentTextIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/invoices') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Faturalar
                </router-link>

                <!-- Group: Yönetim -->
                <div class="mt-6 mb-3 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Yönetim
                </div>
                <router-link to="/users" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:bg-slate-700/50 hover:text-white hover:shadow-md" :class="isActive('/users') ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'text-slate-300'">
                    <component :is="icons.UserGroupIcon" class="mr-3 h-5 w-5 transition-colors" :class="isActive('/users') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300'" />
                    Personel
                </router-link>
            </nav>
        </div>
        
        <!-- Bottom: User Info / Logout -->
        <div class="border-t border-slate-700/50 p-4 bg-slate-800/50 backdrop-blur-sm">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                        <span class="text-white font-semibold text-sm">{{ currentUserInitial }}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">
                        {{ currentUserName || 'Kullanıcı' }}
                    </p>
                    <p class="text-xs text-slate-400 truncate">
                        {{ currentUserRole || 'Kullanıcı' }}
                    </p>
                </div>
                <button @click="logout" class="flex-shrink-0 p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/50 transition-all duration-200" title="Çıkış Yap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { 
    HomeIcon, CubeIcon, ArrowsRightLeftIcon, ClipboardDocumentCheckIcon, 
    TruckIcon, ShoppingCartIcon, BanknotesIcon, UsersIcon, 
    DocumentTextIcon, UserGroupIcon, DocumentMagnifyingGlassIcon, TagIcon,
    CurrencyDollarIcon, ArrowPathRoundedSquareIcon, ChartBarIcon, ArrowUturnLeftIcon
} from '@heroicons/vue/24/outline';
import { useRoute } from 'vue-router'; // Correct way to watch route is setup or watch

export default {
    name: 'Sidebar',
    props: {
        isOpen: {
            type: Boolean,
            default: true
        },
        isMobile: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            icons: {
                HomeIcon, CubeIcon, ArrowsRightLeftIcon, ClipboardDocumentCheckIcon,
                TruckIcon, ShoppingCartIcon, BanknotesIcon, UsersIcon,
                DocumentTextIcon, UserGroupIcon, DocumentMagnifyingGlassIcon, TagIcon,
                CurrencyDollarIcon, ArrowPathRoundedSquareIcon, ChartBarIcon, ArrowUturnLeftIcon
            },
            tenantName: '',
            currentUserName: '',
            currentUserRole: '',
            currentUserInitial: 'U'
        }
    },
    mounted() {
        const userStr = localStorage.getItem('user');
        if (userStr) {
            try {
                const user = JSON.parse(userStr);
                this.tenantName = user.tenant_name || '';
                this.currentUserName = user.name || '';
                this.currentUserRole = user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : '';
                this.currentUserInitial = user.name ? user.name.charAt(0).toUpperCase() : 'U';
            } catch (e) {
                console.error('Error parsing user data', e);
            }
        }
    },
    methods: {
        isActive(path) {
            return this.$route.path === path || this.$route.path.startsWith(path + '/');
        },
        async logout() {
            try {
                // In a real app, call logout API. Here just clear token.
                localStorage.removeItem('token');
                this.$router.push('/login');
            } catch (error) {
                console.error(error);
            }
        }
    }
}
</script>
