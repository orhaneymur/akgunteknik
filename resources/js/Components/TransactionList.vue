<template>
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Hesap Hareketleri</h3>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlem Türü</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ formatDate(transaction.date) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm">
             <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                   :class="transaction.type === 'deposit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
               {{ transaction.type === 'deposit' ? 'Giriş / Alacak' : 'Çıkış / Borç' }}
             </span>
          </td>
          <td class="px-6 py-4 text-sm text-gray-900">
            {{ transaction.description || '-' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold" 
              :class="transaction.type === 'deposit' ? 'text-green-600' : 'text-red-600'">
            {{ transaction.type === 'deposit' ? '+' : '-' }} {{ formatCurrency(transaction.amount, transaction.currency) }}
          </td>
        </tr>
        <tr v-if="transactions.length === 0">
            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Kayıtlı hareket bulunamadı.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';
import dayjs from 'dayjs';

export default {
  props: {
    payableType: {
      type: String,
      required: true
    },
    payableId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      transactions: []
    }
  },
  watch: {
    payableId: {
      immediate: true,
      handler(newVal) {
        if (newVal) this.fetchTransactions();
      }
    }
  },
  methods: {
    formatDate(date) {
      return dayjs(date).format('DD.MM.YYYY');
    },
    formatCurrency(value, currency = 'TRY') {
      if (!value) value = 0;
      return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: currency }).format(value);
    },
    async fetchTransactions() {
      try {
        const response = await axios.get('/api/finance/transactions', {
            params: {
                payable_type: this.payableType,
                payable_id: this.payableId
            }
        });
        this.transactions = response.data.data;
      } catch (error) {
        console.error('Error fetching transactions:', error);
      }
    }
  }
}
</script>
