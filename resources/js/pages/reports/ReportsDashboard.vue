<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-semibold text-gray-900">Raporlar & Analizler</h1>
      <div class="flex space-x-2">
          <router-link to="/reports/sales" class="px-4 py-2 bg-white text-indigo-600 rounded-md shadow-sm text-sm font-medium hover:bg-gray-50">
              Satış Raporu
          </router-link>
          <router-link to="/reports/stock" class="px-4 py-2 bg-white text-indigo-600 rounded-md shadow-sm text-sm font-medium hover:bg-gray-50">
              Stok Raporu
          </router-link>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Sales This Month -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
               <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
               </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Bu Ay Satış</dt>
                <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(stats.sales_month) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Expenses This Month -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
               <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
               </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Bu Ay Gider</dt>
                <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(stats.expenses_month) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

       <!-- Profit This Month -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
               <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
               </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Tahmini Kar (Nakit)</dt>
                <dd class="text-lg font-medium text-gray-900" :class="stats.profit_month >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ formatCurrency(stats.profit_month) }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

       <!-- Critical Stock -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
               <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
               </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Kritik Stok</dt>
                <dd class="text-lg font-medium text-gray-900">{{ stats.low_stock_count }} Ürün</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Last 7 Days Sales Chart (Simple Bar) -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Son 7 Günlük Satış</h3>
        <div class="relative h-64 flex items-end justify-between space-x-2">
            <div v-for="day in stats.daily_sales" :key="day.date" class="flex flex-col items-center flex-1">
                <div class="w-full bg-indigo-200 rounded-t hover:bg-indigo-300 transition-colors relative group"
                     :style="{ height: getBarHeight(day.total) + '%' }">
                     <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity">
                         {{ formatCurrency(day.total) }}
                     </span>
                </div>
                <div class="text-xs text-gray-500 mt-2 transform -rotate-45 origin-top-left">{{ formatDate(day.date) }}</div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import dayjs from 'dayjs';

export default {
    data() {
        return {
            stats: {
                sales_month: 0,
                expenses_month: 0,
                profit_month: 0,
                low_stock_count: 0,
                daily_sales: [],
                open_orders_count: 0
            }
        }
    },
    mounted() {
        this.fetchStats();
    },
    methods: {
        async fetchStats() {
            try {
                const response = await axios.get('/api/core/reports/dashboard-stats');
                this.stats = response.data;
            } catch (error) {
                console.error('Error fetching stats:', error);
            }
        },
        formatCurrency(value) {
            if (!value) value = 0;
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
        },
        formatDate(date) {
            return dayjs(date).format('DD.MM');
        },
        getBarHeight(value) {
            const max = Math.max(...this.stats.daily_sales.map(d => parseFloat(d.total))) || 1;
            return Math.min((value / max) * 100, 100);
        }
    }
}
</script>
