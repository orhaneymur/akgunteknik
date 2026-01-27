<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Satışlar
            </h2>
            <div class="flex">
                <router-link to="/sales/create" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Yeni Satış Yap
                </router-link>
            </div>
        </div>

        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sipariş No
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Müşteri
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
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="order in orders" :key="order.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ order.id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ order.customer ? order.customer.name : 'Genel Müşteri' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                    {{ formatCurrency(order.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="viewDetails(order)" class="text-blue-600 hover:text-blue-900 mr-4">İncele</button>
                                    <button @click="confirmInvoice(order)" class="text-indigo-600 hover:text-indigo-900">Faturalaştır</button>
                                </td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Henüz satış yapılmamış.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <div v-if="showModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Sipariş Detayı #{{ selectedOrder.id }}
                        </h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-2">
                                <strong>Müşteri:</strong> {{ selectedOrder.customer ? selectedOrder.customer.name : 'Genel Müşteri' }}
                            </p>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ürün</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Adet</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Birim Fiyat</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Toplam</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in selectedOrder.items" :key="item.id">
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ item.product ? item.product.name : 'Silinmiş Ürün' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ item.quantity }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 text-right font-medium">{{ formatCurrency(item.total_price) }}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50">
                                        <td colspan="3" class="px-4 py-2 text-right font-bold text-gray-900">Genel Toplam:</td>
                                        <td class="px-4 py-2 text-right font-bold text-gray-900">{{ formatCurrency(selectedOrder.total_amount) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                     <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="confirmInvoice(selectedOrder)" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Faturalaştır
                        </button>
                        <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Kapat
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
            orders: [],
            selectedOrder: null,
            showModal: false
        };
    },
    mounted() {
        this.fetchOrders();
    },
    methods: {
        async fetchOrders() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/sales/orders', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.orders = response.data.data;
                }
            } catch (error) {
                console.error(error);
            }
        },
        viewDetails(order) {
            this.selectedOrder = order;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.selectedOrder = null;
        },
        async confirmInvoice(order) {
            if (!confirm(`Sipariş #${order.id} için fatura oluşturmak istediğinize emin misiniz?`)) return;
            await this.createInvoice(order.id);
        },
        async createInvoice(orderId) {
             try {
                const token = localStorage.getItem('token');
                const response = await axios.post('/api/finance/invoices/from-source', {
                    source_type: 'order',
                    source_id: orderId
                }, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                
                if (response.data.success) {
                    alert('Fatura oluşturuldu!');
                    this.$router.push(`/invoices/${response.data.data.id}`);
                }
            } catch (error) {
                if (error.response && error.response.status === 409) {
                     alert('Bu sipariş için zaten bir fatura mevcut #' + error.response.data.data.invoice_id);
                     this.$router.push(`/invoices/${error.response.data.data.invoice_id}`);
                } else {
                    console.error(error);
                    const msg = error.response?.data?.message || 'Fatura oluşturulamadı.';
                    // API returns generic errors in 'errors' field, not 'data'
                    const debug = error.response?.data?.errors?.error || error.response?.data?.data?.error || '';
                    alert(msg + ' ' + debug);
                }
            }
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
        },
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('tr-TR');
        }
    }
}
</script>
