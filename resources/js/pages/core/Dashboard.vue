<template>
    <div class="py-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Genel Bakış</h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Sales -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Toplam Satış</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(stats.total_sales) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Total Cost -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Toplam Maliyet</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(stats.total_cost) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit -->
             <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Net Kâr</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(stats.total_profit) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Toplam Ürün</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ stats.total_products }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <!-- Recent Orders -->
            <div class="bg-white shadow rounded-lg">
                 <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Son Siparişler</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             <tr v-for="order in stats.recent_orders" :key="order.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ order.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ order.customer ? order.customer.name : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(order.total_amount) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
                            </tr>
                            <tr v-if="!stats.recent_orders || stats.recent_orders.length === 0">
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Henüz sipariş yok.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Top Selling Products -->
                 <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">En Çok Satanlar</h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="(prod, idx) in stats.top_selling" :key="idx" class="px-4 py-4 flex items-center justify-between hover:bg-gray-50">
                             <div class="text-sm font-medium text-gray-900">{{ prod.name }}</div>
                             <div class="text-sm text-gray-500">{{ prod.qty }} Adet</div>
                        </li>
                         <li v-if="!stats.top_selling || stats.top_selling.length === 0" class="px-4 py-4 text-center text-sm text-gray-500">
                            Veri yok.
                        </li>
                    </ul>
                </div>

                <!-- Low Stock Alert -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-red-600">Kritik Stok Uyarısı (< 10)</h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="product in stats.low_stock_products" :key="product.id" class="px-4 py-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                            <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ product.current_stock }} Adet
                            </div>
                        </li>
                        <li v-if="!stats.low_stock_products || stats.low_stock_products.length === 0" class="px-4 py-4 text-center text-sm text-gray-500">
                            Kritik seviyede ürün yok.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            stats: {
                total_sales: 0,
                total_cost: 0,
                total_profit: 0,
                total_customers: 0,
                total_products: 0,
                recent_orders: [],
                low_stock_products: [],
                top_selling: []
            }
        }
    },
    mounted() {
        this.fetchStats();
    },
    methods: {
        async fetchStats() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/core/dashboard/stats', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.stats = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching dashboard stats:', error);
            }
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('tr-TR');
        }
    }
}
</script>
