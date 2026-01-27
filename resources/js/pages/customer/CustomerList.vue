<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Müşteriler
            </h2>
            <router-link to="/customers/create" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Yeni Müşteri Ekle
            </router-link>
        </div>

        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        İsim
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        İletişim
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Bakiye
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Düzenle</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="customer in customers" :key="customer.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                                        <div class="text-sm text-gray-500">{{ customer.tax_number ? 'VKN: ' + customer.tax_number : '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ customer.phone }}</div>
                                        <div class="text-sm text-gray-500">{{ customer.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                            :class="customer.current_balance < 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                                            {{ customer.current_balance }} ₺
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <router-link :to="{ name: 'CustomerEdit', params: { id: customer.id } }" class="text-indigo-600 hover:text-indigo-900 mr-3">Düzenle</router-link>
                                        <router-link :to="{ name: 'CustomerStatement', params: { id: customer.id } }" class="text-green-600 hover:text-green-900 mr-3">Ekstre</router-link>
                                        <button @click="deleteCustomer(customer.id)" class="text-red-600 hover:text-red-900">Sil</button>
                                    </td>
                                </tr>
                                <tr v-if="customers.length === 0">
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        Henüz müşteri kaydı yok.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
            customers: []
        }
    },
    mounted() {
        this.fetchCustomers();
    },
    methods: {
        async fetchCustomers() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/customers/customers', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.customers = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching customers:', error);
            }
        }
    }
}
</script>
