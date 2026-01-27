<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Giderler</h2>
        <p class="text-sm text-gray-500">Şirket harcamalarını takip edin</p>
      </div>
      <button @click="openModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
        + Yeni Gider Ekle
      </button>
    </div>

    <!-- Expense List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ödeme Kaynağı</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(expense.date) }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              {{ expense.description }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
               <span v-if="expense.category" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                     :style="{ backgroundColor: expense.category.color + '20', color: expense.category.color }">
                 {{ expense.category.name }}
               </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
               <span v-if="expense.safe">{{ expense.safe.name }}</span>
               <span v-else class="text-gray-400">-</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
              {{ formatCurrency(expense.amount) }}
            </td>
          </tr>
          <tr v-if="expenses.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">Kayıtlı gider bulunamadı.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Yeni Gider Ekle
            </h3>
            <div class="mt-4 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Açıklama</label>
                <input v-model="form.description" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
              </div>

              <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Tutar</label>
                    <input v-model="form.amount" type="number" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Tarih</label>
                    <input v-model="form.date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                  </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                <select v-model="form.category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Ödeme Yapılan Kasa/Hesap</label>
                <select v-model="form.safe_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    <option :value="null">Kasadan Düşme (Sadece Kayıt)</option>
                    <option v-for="safe in safes" :key="safe.id" :value="safe.id">{{ safe.name }} ({{ formatCurrency(safe.balance, safe.currency) }})</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Eğer bir kasa seçerseniz, bakiye otomatik düşülecektir.</p>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="saveExpense" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
              Kaydet
            </button>
            <button @click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              İptal
            </button>
          </div>
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
      expenses: [],
      categories: [],
      safes: [],
      showModal: false,
      form: {
        description: '',
        amount: '',
        date: new Date().toISOString().substr(0, 10),
        category_id: null,
        safe_id: null
      }
    }
  },
  mounted() {
    this.fetchExpenses();
    this.fetchCategories();
    this.fetchSafes();
  },
  methods: {
    formatDate(date) {
      return dayjs(date).format('DD.MM.YYYY');
    },
    formatCurrency(value, currency = 'TRY') {
      if (!value) value = 0;
      return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: currency }).format(value);
    },
    async fetchExpenses() {
      try {
        const response = await axios.get('/api/finance/expenses');
        this.expenses = response.data;
      } catch (error) {
        console.error('Error fetching expenses:', error);
      }
    },
    async fetchCategories() {
        try {
            const response = await axios.get('/api/finance/expense-categories');
            this.categories = response.data;
        } catch (error) {
            console.error(error);
        }
    },
    async fetchSafes() {
        try {
            const response = await axios.get('/api/finance/safes');
            this.safes = response.data;
        } catch (error) {
            console.error(error);
        }
    },
    openModal() {
      this.form = {
        description: '',
        amount: '',
        date: new Date().toISOString().substr(0, 10),
        category_id: this.categories.length > 0 ? this.categories[0].id : null,
        safe_id: null
      };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async saveExpense() {
      try {
        await axios.post('/api/finance/expenses', this.form);
        await this.fetchExpenses();
        // Refresh safes if one was used, need global state or just re-fetch here if we list safes somewhere else?
        // Actually this component doesn't show safe balances in the list but the modal does. 
        // We'll re-fetch safes next time modal opens or now.
        this.fetchSafes(); 
        this.closeModal();
      } catch (error) {
        console.error('Error saving expense:', error);
        alert('Gider kaydedilemedi.');
      }
    }
  }
}
</script>
