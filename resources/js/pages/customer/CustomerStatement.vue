<template>
  <div class="space-y-6">
    <ErrorAlert :error="error" @dismiss="error = null" />
    <LoadingSpinner :show="loading" />
    
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <div>
        <h2 class="text-xl font-bold text-gray-800" v-if="customer">{{ customer.name }} - Hesap Ekstresi</h2>
        <p class="text-sm text-gray-500" v-if="customer">{{ customer.company_name }}</p>
      </div>
      <router-link to="/customers" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">
        Geri Dön
      </router-link>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" v-if="customer">
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h4 class="text-sm font-medium text-gray-500">Mevcut Bakiye</h4>
            <p class="text-2xl font-bold mt-2 text-gray-900">0.00 ₺</p>
            <p class="text-xs text-blue-500 mt-1">Hesaplama özelliği yakında</p>
        </div>
    </div>

    <!-- Transaction List -->
    <TransactionList 
        v-if="customer" 
        :payable-type="'Modules\\Customer\\Models\\Customer'" 
        :payable-id="customer.id" 
    />
  </div>
</template>

<script>
import apiClient from '../../api/client.js';
import ErrorAlert from '../../Components/ErrorAlert.vue';
import LoadingSpinner from '../../Components/LoadingSpinner.vue';
import TransactionList from '../../components/TransactionList.vue';

export default {
  components: {
    TransactionList,
    ErrorAlert,
    LoadingSpinner
  },
  data() {
    return {
      customer: null,
      loading: false,
      error: null
    }
  },
  async mounted() {
    const customerId = this.$route.params.id;
    await this.fetchCustomer(customerId);
  },
  methods: {
    async fetchCustomer(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await apiClient.get(`/customers/customers/${id}`);
        if (response.data.success) {
          this.customer = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching customer:', error);
        this.error = error.response?.data?.message || 'Müşteri bilgileri yüklenemedi.';
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>
