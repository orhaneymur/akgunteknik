<template>
    <div class="flex flex-col flex-shrink-0 w-64 text-white bg-gray-900 transition-all duration-300" :class="{ '-ml-64': !isOpen && isMobile }">
        <!-- Logo Area -->
        <div class="flex items-center justify-center h-16 bg-gray-900 shadow-md">
            <span class="text-2xl font-bold tracking-wider uppercase text-indigo-400">Has Panel</span>
        </div>

        <!-- User Profile (Mobile Only - Optional, skipping for now to keep clean) -->

        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto">
            <nav class="px-2 py-4 space-y-1">
                
                <!-- Group: Finans -->
                <div class="mt-4 mb-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Finans
                </div>
                <router-link to="/finance/expenses" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/finance/expenses') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.CurrencyDollarIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Giderler
                </router-link>
                <router-link to="/finance/safes" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/finance/safes') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.BanknotesIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Kasa & Banka
                </router-link>
                <router-link to="/finance/categories" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/finance/categories') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.TagIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Gider Kategorileri
                </router-link>

                <!-- Group: Genel Bakış -->
                <div class="mt-4 mb-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Genel Bakış
                </div>
                <router-link to="/dashboard" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.HomeIcon" class="mr-4 h-6 w-6 text-indigo-400 group-hover:text-indigo-300" />
                    Dashboard
                </router-link>
                <router-link to="/reports" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/reports') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.ChartBarIcon" class="mr-4 h-6 w-6 text-indigo-400 group-hover:text-indigo-300" />
                    Raporlar
                </router-link>

                <!-- Group: Stok Yönetimi -->
                <div class="mt-8 mb-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Stok Yönetimi
                </div>
                <router-link to="/products" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/products') && !isActive('/products/movements') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.CubeIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Ürün Listesi
                </router-link>
                <router-link to="/products/movements" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/products/movements') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.ArrowsRightLeftIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Stok Hareketleri
                </router-link>
                 <router-link to="/purchase-orders" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/purchase-orders') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.ClipboardDocumentCheckIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Alış Siparişleri
                </router-link>
                <router-link to="/suppliers" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/suppliers') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.TruckIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Tedarikçiler
                </router-link>
                <router-link to="/transfers" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/transfers') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.ArrowPathRoundedSquareIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Depo Transferi
                </router-link>
                <router-link to="/returns" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/returns') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.ArrowUturnLeftIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    İade & Geri Alım
                </router-link>

                <!-- Group: Satış & Müşteri -->
                <div class="mt-8 mb-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Satış & Müşteri
                </div>
                <router-link to="/sales/create" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/sales/create') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.ShoppingCartIcon" class="mr-4 h-6 w-6 text-green-400 group-hover:text-green-300" />
                    Hızlı Satış Yap
                </router-link>
                <router-link to="/sales" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/sales') && !isActive('/sales/create') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.BanknotesIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Satış Listesi
                </router-link>
                <router-link to="/customers" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/customers') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.UsersIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Müşteriler
                </router-link>
                <router-link to="/quotes" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/quotes') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.DocumentMagnifyingGlassIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Teklifler
                </router-link>

                <!-- Group: Finans -->
                <div class="mt-8 mb-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Finans
                </div>
                <router-link to="/invoices" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/invoices') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.DocumentTextIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Faturalar
                </router-link>

                <!-- Group: Yönetim -->
                <div class="mt-8 mb-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Yönetim
                </div>
                <router-link to="/users" class="group flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-800 hover:text-white" :class="isActive('/users') ? 'bg-gray-800 text-white' : 'text-gray-300'">
                    <component :is="icons.UserGroupIcon" class="mr-4 h-6 w-6 text-gray-400 group-hover:text-gray-300" />
                    Personel
                </router-link>
            </nav>
        </div>
        
        <!-- Bottom: User Info / Logout -->
        <div class="border-t border-gray-800 p-4">
            <div class="flex items-center">
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">
                        Kullanıcı
                    </p>
                    <p class="text-xs font-medium text-gray-400 group-hover:text-gray-300 cursor-pointer hover:text-white" @click="logout">
                        Çıkış Yap
                    </p>
                </div>
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
