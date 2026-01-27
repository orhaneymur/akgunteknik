<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Depo Transferleri</h2>
        <p class="text-sm text-gray-500">Depolar arası stok hareketlerini yönetin</p>
      </div>
      <router-link to="/transfers/create" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
        + Yeni Transfer
      </router-link>
    </div>

    <!-- Transfer List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kaynak Depo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hedef Depo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notlar</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="transfer in transfers" :key="transfer.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(transfer.date) }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              {{ transfer.from_warehouse ? transfer.from_warehouse.name : '-' }}
            </td>
             <td class="px-6 py-4 text-sm text-gray-900">
              {{ transfer.to_warehouse ? transfer.to_warehouse.name : '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
               <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                     :class="{
                        'bg-yellow-100 text-yellow-800': transfer.status === 'pending',
                        'bg-green-100 text-green-800': transfer.status === 'completed',
                        'bg-red-100 text-red-800': transfer.status === 'cancelled'
                     }">
                 {{ formatStatus(transfer.status) }}
               </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">
              {{ transfer.notes || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
               <button v-if="transfer.status === 'pending'" @click="completeTransfer(transfer.id)" class="text-green-600 hover:text-green-900 mr-3">Tamamla</button>
               <button v-if="transfer.status === 'pending'" @click="cancelTransfer(transfer.id)" class="text-red-600 hover:text-red-900">İptal</button>
            </td>
          </tr>
          <tr v-if="transfers.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">Kayıtlı transfer bulunamadı.</td>
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
      transfers: []
    }
  },
  mounted() {
    this.fetchTransfers();
  },
  methods: {
    formatDate(date) {
      return dayjs(date).format('DD.MM.YYYY');
    },
    formatStatus(status) {
        const map = {
            'pending': 'Bekliyor',
            'completed': 'Tamamlandı',
            'cancelled': 'İptal Edildi'
        };
        return map[status] || status;
    },
    async fetchTransfers() {
      try {
        const response = await axios.get('/api/inventory-transfers');
        this.transfers = response.data.data;
      } catch (error) {
        console.error('Error fetching transfers:', error);
      }
    },
    async completeTransfer(id) {
        if (!confirm('Bu transferi tamamlamak istediğinize emin misiniz? Stoklar güncellenecektir.')) return;
        try {
            await axios.post(`/api/inventory-transfers/${id}/complete`);
            this.fetchTransfers();
            alert('Transfer başarıyla tamamlandı.');
        } catch (error) {
            console.error('Error completing transfer:', error);
            alert('İşlem başarısız.');
        }
    },
    async cancelTransfer(id) {
        if (!confirm('Bu transferi iptal etmek istediğinize emin misiniz?')) return;
        try {
             await axios.post(`/api/inventory-transfers/${id}/cancel`);
             this.fetchTransfers();
        } catch (error) {
             console.error('Error cancelling transfer:', error);
        }
    }
  }
}
</script>
