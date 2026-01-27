<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Faturalar
            </h2>
            <!-- <div class="flex">
                <router-link to="/invoices/create" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Yeni Fatura
                </router-link>
            </div> -->
        </div>

        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fatura No
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Muhatap
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tarih
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tutar
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Durum
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">View</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="invoice in invoices" :key="invoice.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                    <router-link :to="`/invoices/${invoice.id}`">{{ invoice.invoice_number }}</router-link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ invoice.contact_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(invoice.issue_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                    {{ formatCurrency(invoice.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(invoice.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ getStatusLabel(invoice.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <router-link :to="`/invoices/${invoice.id}`" class="text-indigo-600 hover:text-indigo-900">Görüntüle</router-link>
                                </td>
                            </tr>
                             <tr v-if="invoices.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Henüz fatura oluşturulmamış.</td>
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
            invoices: []
        };
    },
    mounted() {
        this.fetchInvoices();
    },
    methods: {
        async fetchInvoices() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/finance/invoices', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.invoices = response.data.data.data;
                }
            } catch (error) {
                console.error(error);
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('tr-TR');
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
        },
        getStatusLabel(status) {
            const labels = { 'draft': 'Taslak', 'sent': 'Gönderildi', 'paid': 'Ödendi', 'cancelled': 'İptal' };
            return labels[status] || status;
        },
        getStatusClass(status) {
             const classes = {
                'draft': 'bg-gray-100 text-gray-800',
                'sent': 'bg-blue-100 text-blue-800',
                'paid': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }
    }
}
</script>
