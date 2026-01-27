<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <h2 class="text-xl font-bold text-gray-800">Stok Değer ve Analiz Raporu</h2>
      <div class="flex items-center space-x-4">
          <div class="text-right">
              <span class="block text-xs text-gray-500">Toplam Stok Değeri</span>
              <span class="block text-xl font-bold text-indigo-600">{{ formatCurrency(report.total_stock_value) }}</span>
          </div>
      </div>
    </div>

    <!-- Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Critical Stock -->
        <div class="bg-white rounded-lg shadow overflow-hidden border-l-4 border-red-500">
             <div class="px-6 py-4 border-b border-gray-200 bg-red-50 flex justify-between items-center">
                <h3 class="text-lg font-medium text-red-800">Kritik Stok Seviyeleri</h3>
                <span class="bg-red-200 text-red-800 text-xs px-2 py-1 rounded">{{ report.critical_stock.length }} Ürün</span>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ürün</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mevcut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Min. Seviye</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="product in report.critical_stock" :key="product.id">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ product.name }}</td>
                        <td class="px-6 py-4 text-sm text-right font-bold text-red-600">{{ product.stock_quantity }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-500">{{ product.min_stock_level }}</td>
                    </tr>
                    <tr v-if="report.critical_stock.length === 0">
                        <td colspan="3" class="px-6 py-4 text-center text-green-600">Harika! Kritik seviyede ürün yok.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- High Value Stock -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
             <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">En Yüksek Değerli Stoklar</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ürün</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Miktar</th>
                         <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Maliyet</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Top. Değer</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in report.top_value_stock" :key="item.id">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ item.name }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-500">{{ item.stock_quantity }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-500">{{ formatCurrency(item.cost_price) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ formatCurrency(item.total_value) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            report: {
                critical_stock: [],
                total_stock_value: 0,
                top_value_stock: []
            }
        }
    },
    mounted() {
        this.fetchReport();
    },
    methods: {
        async fetchReport() {
            try {
                const response = await axios.get('/api/core/reports/stock');
                this.report = response.data;
            } catch (error) {
                console.error('Error fetching stock report:', error);
            }
        },
        formatCurrency(value) {
             return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value || 0);
        }
    }
}
</script>
