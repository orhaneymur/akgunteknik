<template>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Panel
        </h2>
        
        <ErrorAlert :error="error" @dismiss="error = null" />
        <LoadingSpinner :show="loading" />

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Sales -->
            <div class="bg-white/80 backdrop-blur-xl overflow-hidden shadow-lg rounded-xl border border-gray-200/50 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">
                                    Toplam Satış
                                </dt>
                                <dd class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ formattedSales }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Customers -->
            <div class="bg-white/80 backdrop-blur-xl overflow-hidden shadow-lg rounded-xl border border-gray-200/50 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">
                                    Müşteriler
                                </dt>
                                <dd class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ stats.total_customers }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white/80 backdrop-blur-xl overflow-hidden shadow-lg rounded-xl border border-gray-200/50 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">
                                    Ürünler
                                </dt>
                                <dd class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ stats.total_products }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Warning -->
             <div class="bg-white/80 backdrop-blur-xl overflow-hidden shadow-lg rounded-xl border border-gray-200/50 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">
                                    Kritik Stok
                                </dt>
                                <dd class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ stats.low_stock_products ? stats.low_stock_products.length : 0 }} Ürün
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Orders -->
            <div class="bg-white/80 backdrop-blur-xl shadow-lg overflow-hidden rounded-xl border border-gray-200/50">
                <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200/50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Son Siparişler
                    </h3>
                </div>
                <div class="divide-y divide-gray-200/50">
                    <ul class="divide-y divide-gray-200/50">
                        <li v-for="order in stats.recent_orders" :key="order.id" class="px-6 py-4 hover:bg-gray-50/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-indigo-600 truncate">
                                    Sipariş #{{ order.id }}
                                </p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200">
                                        {{ order.status }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-600">
                                        {{ order.customer ? order.customer.name : 'Misafir' }}
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm font-semibold text-gray-900 sm:mt-0">
                                    <span v-html="getOrderAmount(order)"></span>
                                </div>
                            </div>
                        </li>
                         <li v-if="!stats.recent_orders || stats.recent_orders.length === 0" class="px-6 py-8 text-center text-gray-500">
                            Henüz sipariş yok.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="bg-white/80 backdrop-blur-xl shadow-lg overflow-hidden rounded-xl border border-gray-200/50">
                 <div class="px-6 py-5 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-gray-200/50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Stok Uyarısı (< 10)
                    </h3>
                </div>
                <div class="divide-y divide-gray-200/50">
                     <ul class="divide-y divide-gray-200/50">
                        <li v-for="product in stats.low_stock_products" :key="product.id" class="px-6 py-4 hover:bg-gray-50/50 transition-colors">
                             <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-red-600 truncate">
                                    {{ product.name }}
                                </p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border border-red-200">
                                        Stok: {{ product.current_stock }}
                                    </p>
                                </div>
                            </div>
                        </li>
                         <li v-if="!stats.low_stock_products || stats.low_stock_products.length === 0" class="px-6 py-8 text-center text-gray-500">
                            Kritik stok seviyesinde ürün yok.
                        </li>
                     </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import apiClient from '../api/client.js';
import ErrorAlert from '../Components/ErrorAlert.vue';
import LoadingSpinner from '../Components/LoadingSpinner.vue';

export default {
    components: {
        ErrorAlert,
        LoadingSpinner
    },
    data() {
        return {
            stats: {
                total_sales: 0,
                total_customers: 0,
                total_products: 0,
                recent_orders: [],
                low_stock_products: []
            },
            loading: false,
            error: null,
            formattedSales: '$0.00',
            exchangeRate: null,
            orderAmounts: {} // Cache for order amounts
        };
    },
    mounted() {
        this.fetchExchangeRate();
        this.fetchStats();
    },
    methods: {
        async fetchExchangeRate() {
            try {
                const { fetchExchangeRate } = await import('../utils/currency.js');
                this.exchangeRate = await fetchExchangeRate();
            } catch (error) {
                console.error('Error fetching exchange rate:', error);
            }
        },
        async fetchStats() {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.get('/core/dashboard/stats');
                
                if (response.data.success) {
                    this.stats = response.data.data;
                    // Format sales amount
                    if (this.stats.total_sales) {
                        const { formatWithTry } = await import('../utils/currency.js');
                        this.formattedSales = await formatWithTry(this.stats.total_sales, { primary: 'usd' });
                    }
                    // Format order amounts
                    if (this.stats.recent_orders) {
                        const { formatWithTry } = await import('../utils/currency.js');
                        for (const order of this.stats.recent_orders) {
                            if (order.total_amount) {
                                this.orderAmounts[order.id] = await formatWithTry(order.total_amount, { primary: 'usd' });
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching dashboard stats:', error);
                this.error = error.response?.data?.message || 'Dashboard verileri yüklenirken bir hata oluştu.';
            } finally {
                this.loading = false;
            }
        },
        getOrderAmount(order) {
            if (!order || !order.total_amount) return '$0.00';
            return this.orderAmounts[order.id] || '$' + order.total_amount.toFixed(2);
        }
    }
}
</script>
