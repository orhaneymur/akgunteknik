<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Alış Siparişleri
            </h2>
            <div class="flex">
                <router-link to="/purchase-orders/create" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Yeni Sipariş
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
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tedarikçi
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tutar
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Durum
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Şube
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ order.supplier ? order.supplier.name : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatCurrency(order.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(order.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ order.warehouse ? order.warehouse.name : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button v-if="order.status === 'pending'" @click="receiveOrder(order.id)" class="text-green-600 hover:text-green-900 mr-4">Teslim Al (Stok Ekle)</button>
                                    <button v-if="order.status === 'received'" @click="createInvoice(order.id)" class="text-indigo-600 hover:text-indigo-900">Faturalaştır</button>
                                </td>
                            </tr>
                             <tr v-if="orders.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Henüz sipariş yok.</td>
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
            orders: []
        };
    },
    mounted() {
        this.fetchOrders();
    },
    methods: {
        async fetchOrders() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/inventory/purchase-orders', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.orders = response.data.data;
                }
            } catch (error) {
                console.error(error);
            }
        },
        async receiveOrder(id) {
            if (!confirm('Bu siparişi teslim aldığınızı ve stoklara ekleneceğini onaylıyor musunuz?')) return;
            try {
                const token = localStorage.getItem('token');
                const response = await axios.post(`/api/inventory/purchase-orders/${id}/receive`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.fetchOrders();
                    alert('Sipariş teslim alındı ve stoklar güncellendi.');
                }
            } catch (error) {
                console.error(error);
                alert('İşlem başarısız.');
            }
        },
        async createInvoice(orderId) {
             try {
                const token = localStorage.getItem('token');
                const response = await axios.post('/api/finance/invoices/from-source', {
                    source_type: 'purchase_order',
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
                    alert('Fatura oluşturulamadı.');
                }
            }
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
        },
        getStatusLabel(status) {
            const labels = { 'pending': 'Bekliyor', 'received': 'Teslim Alındı', 'cancelled': 'İptal' };
            return labels[status] || status;
        },
        getStatusClass(status) {
             const classes = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'received': 'bg-green-100 text-green-800',
                'cancelled': 'bg-gray-100 text-gray-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }
    }
}
</script>
