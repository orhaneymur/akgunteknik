<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Kasa & Banka Hesapları</h2>
        <p class="text-sm text-gray-500">Nakit ve banka varlıklarınızı yönetin</p>
      </div>
      <button @click="openModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
        + Yeni Hesap
      </button>
    </div>

    <!-- Safe List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
       <!-- Card for each safe -->
       <div v-for="safe in safes" :key="safe.id" class="bg-white rounded-lg shadow border border-gray-200 p-6 flex flex-col justify-between h-32 relative group">
           <div>
               <div class="flex justify-between items-start">
                   <div>
                       <h3 class="text-lg font-semibold text-gray-900">{{ safe.name }}</h3>
                       <p class="text-xs text-gray-500 uppercase tracking-wide">{{ safe.type === 'cash' ? 'Nakit Kasa' : 'Banka Hesabı' }}</p>
                   </div>
                   <span class="text-2xl font-bold" :class="safe.balance >= 0 ? 'text-green-600' : 'text-red-600'">
                       {{ formatCurrency(safe.balance, safe.currency) }}
                   </span>
               </div>
               <p v-if="safe.iban" class="text-xs text-gray-400 mt-1 truncate" title="IBAN">{{ safe.iban }}</p>
           </div>
           
           <div class="absolute top-4 right-4 hidden group-hover:flex space-x-2">
               <button @click="editSafe(safe)" class="text-indigo-600 hover:text-indigo-800 bg-white rounded-full p-1 shadow">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                   </svg>
               </button>
               <button @click="deleteSafe(safe.id)" class="text-red-600 hover:text-red-800 bg-white rounded-full p-1 shadow">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                   </svg>
               </button>
           </div>
       </div>
    </div>

    <!-- Empty State -->
    <div v-if="safes.length === 0" class="text-center py-10 bg-white rounded-lg border border-gray-200">
        <p class="text-gray-500">Henüz kasa veya banka hesabı tanımlanmamış.</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              {{ isEdit ? 'Hesap Düzenle' : 'Yeni Hesap' }}
            </h3>
            <div class="mt-4 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Hesap Adı</label>
                <input v-model="form.name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Tür</label>
                <select v-model="form.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    <option value="cash">Nakit Kasa</option>
                    <option value="bank">Banka Hesabı</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Para Birimi</label>
                 <select v-model="form.currency" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    <option value="TRY">TRY</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                </select>
              </div>
              <div v-if="form.type === 'bank'">
                <label class="block text-sm font-medium text-gray-700">IBAN</label>
                <input v-model="form.iban" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="saveSafe" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
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

export default {
  data() {
    return {
      safes: [],
      showModal: false,
      isEdit: false,
      form: {
        id: null,
        name: '',
        type: 'cash',
        currency: 'TRY',
        iban: ''
      }
    }
  },
  mounted() {
    this.fetchSafes();
  },
  methods: {
    formatCurrency(value, currency) {
      if (!value) value = 0;
      return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: currency }).format(value);
    },
    async fetchSafes() {
      try {
        const response = await axios.get('/api/finance/safes');
        this.safes = response.data;
      } catch (error) {
        console.error('Error fetching safes:', error);
      }
    },
    openModal() {
      this.isEdit = false;
      this.form = { id: null, name: '', type: 'cash', currency: 'TRY', iban: '' };
      this.showModal = true;
    },
    editSafe(safe) {
      this.isEdit = true;
      this.form = { ...safe };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async saveSafe() {
      try {
        if (this.isEdit) {
            await axios.put(`/api/finance/safes/${this.form.id}`, this.form);
        } else {
            await axios.post('/api/finance/safes', this.form);
        }
        await this.fetchSafes();
        this.closeModal();
      } catch (error) {
        console.error('Error saving safe:', error);
        alert('İşlem başarısız.');
      }
    },
    async deleteSafe(id) {
        if (!confirm('Bu hesabı silmek istediğinize emin misiniz?')) return;
        try {
            await axios.delete(`/api/finance/safes/${id}`);
            this.fetchSafes();
        } catch (error) {
            console.error('Error deleting safe:', error);
        }
    }
  }
}
</script>
