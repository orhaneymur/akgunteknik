<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <div>
        <h2 class="text-xl font-bold text-gray-800">İade & Geri Alım Yönetimi</h2>
        <p class="text-sm text-gray-500">Müşteri ve tedarikçi iade işlemlerini yönetin</p>
      </div>
      <router-link to="/returns/create" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
        + Yeni İade Talebi
      </router-link>
    </div>

    <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        <strong class="font-bold">Hata:</strong>
        <span class="block sm:inline">{{ errorMessage }}</span>
    </div>

    <!-- List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tür</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taraf (Müşteri/Tedarikçi)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tutar</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Durum</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="ret in returns" :key="ret.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(ret.date) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span v-if="ret.type === 'sale_return'" class="text-indigo-600 font-medium">Satış İadesi</span>
                        <span v-else class="text-orange-600 font-medium">Alış İadesi</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span v-if="ret.customer">{{ ret.customer.name }} {{ ret.customer.surname }}</span>
                        <span v-else-if="ret.supplier">{{ ret.supplier.company_name }}</span>
                        <span v-else>-</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                        {{ formatCurrency(ret.total_amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                         <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                             :class="{
                                'bg-yellow-100 text-yellow-800': ret.status === 'pending',
                                'bg-green-100 text-green-800': ret.status === 'approved',
                                'bg-red-100 text-red-800': ret.status === 'rejected'
                             }">
                         {{ formatStatus(ret.status) }}
                       </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button v-if="ret.status === 'pending'" @click="approveReturn(ret.id)" class="text-green-600 hover:text-green-900 font-bold">Onayla</button>
                    </td>
                </tr>
                 <tr v-if="returns.length === 0">
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Kayıtlı iade işlemi bulunamadı.</td>
                </tr>
            </tbody>
        </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import dayjs from 'dayjs';

export default {
    data() {
        return {
            returns: [],
            errorMessage: null
        }
    },
    mounted() {
        console.log('ReturnList mounted');
        this.fetchReturns();
    },
    methods: {
        async fetchReturns() {
            this.errorMessage = null;
            try {
                // Correct route based on InventoryServiceProvider prefix 'api/inventory'
                const response = await axios.get('/api/inventory/returns');
                this.returns = response.data.data;
            } catch (error) {
                console.error(error);
                this.errorMessage = 'Veriler yüklenirken hata oluştu: ' + (error.response?.data?.message || error.message);
            }
        },
        async approveReturn(id) {
            if (!confirm('İadeyi onaylamak istiyor musunuz? Stok ve bakiye güncellenecektir.')) return;
            try {
                // Also fix approval route to be consistent with prefix
                await axios.post(`/api/inventory/returns/${id}/approve`);
                alert('İade onaylandı.');
                this.fetchReturns();
            } catch (error) {
                console.error(error);
                alert('Hata: ' + (error.response?.data?.message || 'İşlem başarısız'));
            }
        },
        formatDate(date) {
            return dayjs(date).format('DD.MM.YYYY');
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value || 0);
        },
        formatStatus(status) {
             const map = { 'pending': 'Onay Bekliyor', 'approved': 'Onaylandı', 'rejected': 'Reddedildi' };
             return map[status] || status;
        }
    }
}
</script>
