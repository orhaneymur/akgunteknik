<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Ürünler
            </h2>
            <router-link to="/products/create" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Yeni Ürün Ekle
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
                                        Ürün Adı
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        SKU
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Barkod
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fiyat
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Durum
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                        <div class="text-sm text-gray-500">{{ product.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ product.sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ product.barcode || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ product.base_price }} ₺
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ product.is_active ? 'Aktif' : 'Pasif' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <router-link :to="`/products/${product.id}/edit`" class="text-indigo-600 hover:text-indigo-900 mr-4">Düzenle</router-link>
                                        <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-900">Sil</button>
                                    </td>
                                </tr>
                                <tr v-if="products.length === 0">
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        Henüz ürün bulunmuyor.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
                        Toplam <span class="font-medium">{{ pagination.total }}</span> üründen <span class="font-medium">{{ pagination.from }}</span> ile <span class="font-medium">{{ pagination.to }}</span> arası gösteriliyor.
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
                        
                        <!-- Simple Page Display -->
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
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            products: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                prev_page_url: null,
                next_page_url: null,
                total: 0,
                from: 0,
                to: 0
            }
        }
    },
    mounted() {
        this.fetchProducts();
    },
    methods: {
        async deleteProduct(id) {
            if (confirm('DİKKAT: Bu ürünü silerseniz, ürüne ait TÜM STOK GEÇMİŞİ VE HAREKETLERİ de silinecektir.\n\nBu işlem geri alınamaz. Devam etmek istiyor musunuz?')) {
                try {
                    const token = localStorage.getItem('token');
                    await axios.delete(`/api/inventory/products/${id}`, {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    });
                    this.fetchProducts(this.pagination.current_page); // Refresh current page
                } catch (error) {
                    console.error('Error deleting product:', error);
                    alert('Silme işlemi başarısız oldu.');
                }
            }
        },
        async fetchProducts(page = 1) {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get(`/api/inventory/products?page=${page}`, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                });
                if (response.data.success) {
                    this.products = response.data.data.data;
                    this.pagination = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching products:', error);
                if (error.response && error.response.status === 401) {
                    this.$router.push('/login');
                }
            }
        }
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.fetchProducts(page);
            }
        }
    }
</script>
