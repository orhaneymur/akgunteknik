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

    <!-- Search and Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 space-y-4">
      <div class="relative">
        <input
          type="text"
          v-model="searchQuery"
          @input="debounceSearch"
          placeholder="Açıklama ile ara..."
          class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        />
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
          <select v-model="filters.category_id" @change="fetchExpenses" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
            <option value="">Tümü</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Tarihi</label>
          <input v-model="filters.start_date" @change="fetchExpenses" type="date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi</label>
          <input v-model="filters.end_date" @change="fetchExpenses" type="date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
        </div>
      </div>
    </div>

    <ErrorAlert :error="error" @dismiss="error = null" />
    <LoadingSpinner :show="loading" />

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
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">{{ searchQuery || filters.category_id || filters.start_date || filters.end_date ? 'Arama sonucu bulunamadı.' : 'Kayıtlı gider bulunamadı.' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4" v-if="pagination.total > 0">
      <div class="flex flex-1 justify-between sm:hidden">
        <button @click="changePage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" :class="{'opacity-50 cursor-not-allowed': !pagination.prev_page_url}">
          Önceki
        </button>
        <button @click="changePage(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" :class="{'opacity-50 cursor-not-allowed': !pagination.next_page_url}">
          Sonraki
        </button>
      </div>
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Toplam <span class="font-medium">{{ pagination.total }}</span> giderden <span class="font-medium">{{ pagination.from }}</span> ile <span class="font-medium">{{ pagination.to }}</span> arası gösteriliyor.
          </p>
        </div>
        <div>
          <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
            <button @click="changePage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0" :class="{'opacity-50 cursor-not-allowed': !pagination.prev_page_url}">
              <span class="sr-only">Önceki</span>
              <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
              </svg>
            </button>
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">
              Sayfa {{ pagination.current_page }} / {{ pagination.last_page }}
            </span>
            <button @click="changePage(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0" :class="{'opacity-50 cursor-not-allowed': !pagination.next_page_url}">
              <span class="sr-only">Sonraki</span>
              <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <div class="relative inline-block bg-white rounded-lg text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
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
import apiClient from '../../api/client.js';
import toast from '../../utils/toast.js';
import ErrorAlert from '../../Components/ErrorAlert.vue';
import LoadingSpinner from '../../Components/LoadingSpinner.vue';
import dayjs from 'dayjs';

export default {
  components: {
    ErrorAlert,
    LoadingSpinner
  },
  data() {
    return {
      expenses: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        prev_page_url: null,
        next_page_url: null,
        total: 0,
        from: 0,
        to: 0
      },
      categories: [],
      safes: [],
      showModal: false,
      searchQuery: '',
      searchTimeout: null,
      filters: {
        category_id: '',
        start_date: '',
        end_date: ''
      },
      loading: false,
      error: null,
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
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.pagination.current_page = 1;
        this.fetchExpenses();
      }, 500);
    },
    formatDate(date) {
      return dayjs(date).format('DD.MM.YYYY');
    },
    formatCurrency(value, currency = 'TRY') {
      if (!value) value = 0;
      return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: currency }).format(value);
    },
    async fetchExpenses(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const params = { page };
        if (this.searchQuery) {
          params.search = this.searchQuery;
        }
        if (this.filters.category_id) {
          params.category_id = this.filters.category_id;
        }
        if (this.filters.start_date) {
          params.start_date = this.filters.start_date;
        }
        if (this.filters.end_date) {
          params.end_date = this.filters.end_date;
        }
        const response = await apiClient.get('/finance/expenses', { params });
        if (response.data.success) {
          if (response.data.data.data) {
            // Paginated response
            this.expenses = response.data.data.data;
            this.pagination = response.data.data;
          } else {
            // Non-paginated response (backward compatibility)
            this.expenses = response.data.data;
            this.pagination = {
              current_page: 1,
              last_page: 1,
              total: this.expenses.length,
              from: 1,
              to: this.expenses.length
            };
          }
        }
      } catch (error) {
        console.error('Error fetching expenses:', error);
        this.error = error.response?.data?.message || 'Giderler yüklenirken bir hata oluştu.';
      } finally {
        this.loading = false;
      }
    },
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.fetchExpenses(page);
      }
    },
    async fetchCategories() {
      try {
        const response = await apiClient.get('/finance/expense-categories');
        if (response.data.success) {
          this.categories = response.data.data;
        }
      } catch (error) {
        console.error(error);
      }
    },
    async fetchSafes() {
      try {
        const response = await apiClient.get('/finance/safes');
        if (response.data.success) {
          this.safes = response.data.data;
        }
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
      this.error = null;
      try {
        await apiClient.post('/finance/expenses', this.form);
        toast.success('Gider başarıyla eklendi.');
        await this.fetchExpenses(this.pagination.current_page);
        this.fetchSafes(); 
        this.closeModal();
      } catch (error) {
        console.error('Error saving expense:', error);
        this.error = error.response?.data?.message || 'Gider kaydedilemedi.';
        toast.error(this.error);
      }
    }
  }
}
</script>
