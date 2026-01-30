<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold leading-tight bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                    Ürünler
                </h2>
                <p class="mt-1 text-sm text-gray-500">Ürünlerinizi yönetin ve stok takibi yapın</p>
            </div>
            <router-link to="/products/create" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Ürün Ekle
            </router-link>
        </div>

        <!-- Search and Filters -->
        <div class="mb-4 space-y-4">
            <!-- Search Bar -->
            <div class="relative">
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="debounceSearch"
                    placeholder="Ürün adı, SKU, barkod veya açıklama ile ara..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="filter_category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select id="filter_category" v-model="filters.category_id" @change="applyFilters"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        <option value="">Tümü</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="filter_brand" class="block text-sm font-medium text-gray-700 mb-1">Marka</label>
                    <select id="filter_brand" v-model="filters.brand_id" @change="applyFilters"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        <option value="">Tümü</option>
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                            {{ brand.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="filter_model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                    <select id="filter_model" v-model="filters.model_id" @change="applyFilters" :disabled="!filters.brand_id"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"
                        :class="{'bg-gray-100': !filters.brand_id}">
                        <option value="">Tümü</option>
                        <option v-for="model in filteredModels" :key="model.id" :value="model.id">
                            {{ model.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <ErrorAlert :error="error" @dismiss="error = null" />

        <LoadingSpinner :show="loading" />

        <ConfirmationModal
            :show="showDeleteConfirm"
            title="Ürün Silme Onayı"
            message="DİKKAT: Bu ürünü silerseniz, ürüne ait TÜM STOK GEÇMİŞİ VE HAREKETLERİ de silinecektir. Bu işlem geri alınamaz. Devam etmek istiyor musunuz?"
            type="danger"
            confirm-text="Evet, Sil"
            cancel-text="İptal"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />

        <div class="flex flex-col" v-if="!loading">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="bg-white/80 backdrop-blur-xl shadow-lg overflow-hidden border border-gray-200/50 sm:rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200/50">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ürün Adı
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kategori / Marka / Model
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
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <span v-if="product.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                                {{ product.category.name }}
                                            </span>
                                            <span v-if="product.brand" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-1">
                                                {{ product.brand.name }}
                                            </span>
                                            <span v-if="product.model" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                                {{ product.model.name }}
                                            </span>
                                            <span v-if="!product.category && !product.brand && !product.model" class="text-gray-400">-</span>
                                        </div>
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
                                        <router-link :to="`/products/${product.id}/edit`" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors mr-2">Düzenle</router-link>
                                        <button @click="deleteProduct(product.id)" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">Sil</button>
                                    </td>
                                </tr>
                                <tr v-if="products.length === 0">
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ searchQuery || filters.category_id || filters.brand_id || filters.model_id ? 'Arama sonucu bulunamadı.' : 'Henüz ürün bulunmuyor.' }}
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
import apiClient from '../../api/client.js';
import ErrorAlert from '../../Components/ErrorAlert.vue';
import LoadingSpinner from '../../Components/LoadingSpinner.vue';
import ConfirmationModal from '../../Components/ConfirmationModal.vue';
import toast from '../../utils/toast.js';

export default {
    components: {
        ErrorAlert,
        LoadingSpinner,
        ConfirmationModal,
    },
    data() {
        return {
            products: [],
            categories: [],
            brands: [],
            models: [],
            filters: {
                category_id: '',
                brand_id: '',
                model_id: ''
            },
            pagination: {
                current_page: 1,
                last_page: 1,
                prev_page_url: null,
                next_page_url: null,
                total: 0,
                from: 0,
                to: 0
            },
            searchQuery: '',
            searchTimeout: null,
            loading: false,
            error: null,
            showDeleteConfirm: false,
            productToDelete: null
        }
    },
    computed: {
        filteredModels() {
            if (!this.filters.brand_id) {
                return [];
            }
            return this.models.filter(model => model.brand_id === parseInt(this.filters.brand_id));
        }
    },
    mounted() {
        this.fetchCategories();
        this.fetchBrands();
        this.fetchModels();
        this.fetchProducts();
    },
    methods: {
        async fetchCategories() {
            try {
                const response = await apiClient.get('/inventory/product-categories');
                if (response.data.success) {
                    this.categories = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching categories:', err);
            }
        },
        async fetchBrands() {
            try {
                const response = await apiClient.get('/inventory/brands');
                if (response.data.success) {
                    this.brands = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching brands:', err);
            }
        },
        async fetchModels() {
            try {
                const response = await apiClient.get('/inventory/product-models');
                if (response.data.success) {
                    this.models = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching models:', err);
            }
        },
        applyFilters() {
            // Reset model if brand changes
            if (!this.filters.brand_id) {
                this.filters.model_id = '';
            }
            // Also fetch models for selected brand
            if (this.filters.brand_id) {
                this.fetchModelsForBrand(this.filters.brand_id);
            }
            this.pagination.current_page = 1;
            this.fetchProducts();
        },
        async fetchModelsForBrand(brandId) {
            try {
                const response = await apiClient.get(`/inventory/product-models?brand_id=${brandId}`);
                if (response.data.success) {
                    // Update models list for the filter dropdown
                    // We keep all models but filter in computed property
                }
            } catch (err) {
                console.error('Error fetching models for brand:', err);
            }
        },
        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.current_page = 1;
                this.fetchProducts();
            }, 500);
        },
        deleteProduct(id) {
            this.productToDelete = id;
            this.showDeleteConfirm = true;
        },
        async confirmDelete() {
            if (!this.productToDelete) return;
            
            this.error = null;
            try {
                await apiClient.delete(`/inventory/products/${this.productToDelete}`);
                this.fetchProducts(this.pagination.current_page);
                toast.success('Ürün başarıyla silindi.');
            } catch (error) {
                console.error('Error deleting product:', error);
                this.error = error.response?.data?.message || 'Silme işlemi başarısız oldu.';
                toast.error(this.error);
            } finally {
                this.showDeleteConfirm = false;
                this.productToDelete = null;
            }
        },
        cancelDelete() {
            this.showDeleteConfirm = false;
            this.productToDelete = null;
        },
        async fetchProducts(page = 1) {
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
                if (this.filters.brand_id) {
                    params.brand_id = this.filters.brand_id;
                }
                if (this.filters.model_id) {
                    params.model_id = this.filters.model_id;
                }
                const response = await apiClient.get('/inventory/products', { params });
                if (response.data.success) {
                    this.products = response.data.data.data;
                    this.pagination = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching products:', error);
                this.error = error.response?.data?.message || 'Ürünler yüklenirken bir hata oluştu.';
            } finally {
                this.loading = false;
            }
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.fetchProducts(page);
            }
        }
    }
}
</script>
