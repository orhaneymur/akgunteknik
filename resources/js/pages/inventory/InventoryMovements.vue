<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Stok Hareketleri
            </h2>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Filtrele</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Belirli bir ürün veya işlem tipine göre hareketleri listeleyin.
                    </p>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                            <label for="product" class="block text-sm font-medium text-gray-700">Ürün</label>
                            <select id="product" v-model="filters.product_id" @change="fetchMovements" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Tümü</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <label for="type" class="block text-sm font-medium text-gray-700">İşlem Tipi</label>
                            <select id="type" v-model="filters.type" @change="fetchMovements" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Tümü</option>
                                <option value="sale">Satış</option>
                                <option value="purchase">Alış</option>
                                <option value="adjustment">Düzeltme</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tarih
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ürün
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    İşlem
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Miktar
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Referans
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="movement in movements.data" :key="movement.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(movement.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ movement.product ? movement.product.name : 'Silinmiş Ürün' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getTypeBadgeClass(movement.type)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ getTypeLabel(movement.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="movement.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ movement.reference_id || '-' }}
                                </td>
                            </tr>
                             <tr v-if="!movements.data || movements.data.length === 0">
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Kayıt bulunamadı.
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
         <div v-if="movements.links" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Toplam
                        <span class="font-medium">{{ movements.total }}</span>
                        kayıttan
                        <span class="font-medium">{{ movements.from }}</span>
                        ile
                        <span class="font-medium">{{ movements.to }}</span>
                        arası gösteriliyor
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                         <button
                            v-for="(link, index) in movements.links"
                            :key="index"
                            @click="fetchMovements(link.url)"
                            :disabled="!link.url || link.active"
                            v-html="link.label"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                            :class="{ 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active, 'text-gray-500 cursor-not-allowed': !link.url }"
                        ></button>
                    </nav>
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
            movements: {
                data: [],
                links: []
            },
            products: [],
            filters: {
                product_id: '',
                type: ''
            }
        };
    },
    mounted() {
        this.fetchProducts();
        this.fetchMovements();
    },
    methods: {
        async fetchProducts() {
             try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/inventory/products', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.products = response.data.data;
                }
            } catch (err) {
                console.error(err);
            }
        },
        async fetchMovements(url = null) {
            try {
                const token = localStorage.getItem('token');
                const pageUrl = url || '/api/inventory/movements';
                
                const response = await axios.get(pageUrl, {
                    headers: { Authorization: `Bearer ${token}` },
                    params: this.filters
                });
                
                if (response.data.success) {
                    this.movements = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching movements:', error);
            }
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('tr-TR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date);
        },
        getTypeLabel(type) {
            const labels = {
                'sale': 'Satış',
                'purchase': 'Alış',
                'adjustment': 'Düzeltme'
            };
            return labels[type] || type;
        },
        getTypeBadgeClass(type) {
            const classes = {
                'sale': 'bg-blue-100 text-blue-800',
                'purchase': 'bg-green-100 text-green-800',
                'adjustment': 'bg-gray-100 text-gray-800'
            };
            return classes[type] || 'bg-gray-100 text-gray-800';
        }
    }
}
</script>
