<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <h2 class="text-xl font-bold text-gray-800">Detaylı Satış Raporu</h2>
      <div class="flex space-x-2">
          <input v-model="filters.start_date" type="date" class="border p-2 rounded text-sm"/>
          <input v-model="filters.end_date" type="date" class="border p-2 rounded text-sm"/>
          <button @click="fetchReport" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
              Filtrele
          </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales By Date Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
             <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Günlük Satışlar</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sipariş Sayısı</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam Tutar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in report.sales_by_date" :key="item.date">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(item.date) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-500">{{ item.count }}</td>
                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ formatCurrency(item.total) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
             <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">En Çok Satan Ürünler (Top 10)</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ürün</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Miktar</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam Gelir</th>
                    </tr>
                </thead>
                 <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in report.top_products" :key="item.product_id">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ item.product ? item.product.name : 'Unknown' }}
                            <div class="text-xs text-gray-400">{{ item.product ? item.product.sku : '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-gray-500">{{ item.total_qty }}</td>
                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ formatCurrency(item.total_revenue) }}</td>
                    </tr>
                </tbody>
            </table>
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
            filters: {
                start_date: dayjs().startOf('month').format('YYYY-MM-DD'),
                end_date: dayjs().endOf('month').format('YYYY-MM-DD')
            },
            report: {
                sales_by_date: [],
                top_products: []
            }
        }
    },
    mounted() {
        this.fetchReport();
    },
    methods: {
        async fetchReport() {
            try {
                const response = await axios.get('/api/core/reports/sales', { params: this.filters });
                this.report = response.data;
            } catch (error) {
                console.error('Error fetching sales report:', error);
            }
        },
        formatCurrency(value) {
             return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value || 0);
        },
        formatDate(date) {
            return dayjs(date).format('DD.MM.YYYY');
        }
    }
}
</script>
