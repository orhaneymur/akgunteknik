<template>
    <div>
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-2xl font-bold leading-tight bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">{{ isEditing ? 'Ürün Düzenle' : 'Yeni Ürün' }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ isEditing ? 'Mevcut ürün bilgilerini güncelleyin.' : 'Stok takibi yapılacak yeni bir ürün tanımlayın.' }}
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form @submit.prevent="saveProduct">
                    <div class="bg-white/80 backdrop-blur-xl shadow-xl sm:rounded-xl sm:overflow-hidden border border-gray-200/50">
                        <div class="px-6 py-6 bg-white/50 space-y-6 sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                                    <select id="category_id" v-model="form.category_id" 
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option :value="null">Kategori Seçiniz</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="brand_id" class="block text-sm font-medium text-gray-700">Marka</label>
                                    <select id="brand_id" v-model="form.brand_id" @change="onBrandChange"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option :value="null">Marka Seçiniz</option>
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="model_id" class="block text-sm font-medium text-gray-700">Model</label>
                                    <select id="model_id" v-model="form.model_id" :disabled="!form.brand_id"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                        :class="{'bg-gray-100': !form.brand_id}">
                                        <option :value="null">Model Seçiniz</option>
                                        <option v-for="model in filteredModels" :key="model.id" :value="model.id">
                                            {{ model.name }}
                                        </option>
                                    </select>
                                    <p v-if="!form.brand_id" class="mt-1 text-xs text-gray-500">Önce marka seçiniz</p>
                                    <p v-else-if="filteredModels.length === 0" class="mt-1 text-xs text-yellow-600">
                                        Bu marka için henüz model tanımlanmamış. Model eklemek için Model Yönetimi sayfasını kullanabilirsiniz.
                                    </p>
                                    <div v-if="form.brand_id && filteredModels.length === 0" class="mt-2">
                                        <button type="button" @click="showQuickAddModel = true" 
                                            class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                            Hızlı Model Ekle
                                        </button>
                                    </div>
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Ürün Adı</label>
                                    <input type="text" name="name" id="name" v-model="form.name" required
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU (Stok Kodu)</label>
                                    <input type="text" name="sku" id="sku" v-model="form.sku" placeholder="Otomatik için boş bırakın"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="barcode" class="block text-sm font-medium text-gray-700">Barkod (Opsiyonel)</label>
                                    <input type="text" name="barcode" id="barcode" v-model="form.barcode" placeholder="Barkod okutunuz..."
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <!-- Prices removed as per user request
                                <div class="col-span-6 sm:col-span-2">
                                    <label for="base_price" class="block text-sm font-medium text-gray-700">Satış Fiyatı (₺) <span class="text-xs text-gray-500">(Opsiyonel)</span></label>
                                    <input type="number" step="0.01" name="base_price" id="base_price" v-model="form.base_price"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-2">
                                    <label for="cost_price" class="block text-sm font-medium text-gray-700">Alış Maliyeti (₺) <span class="text-xs text-gray-500">(Opsiyonel)</span></label>
                                    <input type="number" step="0.01" name="cost_price" id="cost_price" v-model="form.cost_price"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                -->

                                <div class="col-span-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Açıklama</label>
                                    <div class="mt-1">
                                        <textarea id="description" name="description" rows="3" v-model="form.description"
                                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">Ürün hakkında kısa bir açıklama.</p>
                                </div>

                                <div class="col-span-6">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="is_active" name="is_active" type="checkbox" v-model="form.is_active"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="is_active" class="font-medium text-gray-700">Aktif</label>
                                            <p class="text-gray-500">Bu ürün satışta ve işlem yapılabilir.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-6">
                                <label for="compatibles" class="block text-sm font-medium text-gray-700">Muadil / Uyumlu Ürünler</label>
                                <p class="text-xs text-gray-500 mb-2">Çoklu seçim için CTRL tuşuna basılı tutarak seçiniz.</p>
                                <select id="compatibles" multiple v-model="form.compatible_ids" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md h-32">
                                    <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                                        {{ product.name }} ({{ product.sku }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Quick Add Model Modal -->
                        <div v-if="showQuickAddModel" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showQuickAddModel = false">
                            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                <div class="mt-3">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Yeni Model Ekle</h3>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Marka</label>
                                        <input type="text" :value="brands.find(b => b.id == form.brand_id)?.name || ''" disabled
                                            class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm p-2 border">
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Model Adı</label>
                                        <input type="text" v-model="newModelName" placeholder="Örn: Galaxy S21, iPhone 13"
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" @click="showQuickAddModel = false; newModelName = ''"
                                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                            İptal
                                        </button>
                                        <button type="button" @click="quickAddModel"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                            Ekle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ErrorAlert :error="error" @dismiss="error = null" />
                        <LoadingSpinner :show="loading" />

                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100/50 border-t border-gray-200/50 text-right sm:px-6">
                            <button type="button" @click="$router.push('/products')" class="mr-3 inline-flex justify-center py-2.5 px-5 border border-gray-300 shadow-sm text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                İptal
                            </button>
                            <button type="submit"
                                class="inline-flex justify-center py-2.5 px-5 border border-transparent shadow-lg text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                                Kaydet
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import apiClient from '../../api/client.js';
import toast from '../../utils/toast.js';
import ErrorAlert from '../../Components/ErrorAlert.vue';
import LoadingSpinner from '../../Components/LoadingSpinner.vue';

export default {
    components: {
        ErrorAlert,
        LoadingSpinner
    },
    data() {
        return {
            form: {
                category_id: null,
                brand_id: null,
                model_id: null,
                name: '',
                sku: '',
                barcode: '',
                base_price: null,
                cost_price: null,
                description: '',
                is_active: true,
                compatible_ids: []
            },
            categories: [],
            brands: [],
            models: [],
            availableProducts: [],
            error: null,
            isEditing: false,
            loading: false,
            showQuickAddModel: false,
            newModelName: ''
        }
    },
    computed: {
        filteredModels() {
            if (!this.form.brand_id) {
                return [];
            }
            // Convert both to integers for comparison
            const brandId = parseInt(this.form.brand_id);
            return this.models.filter(model => parseInt(model.brand_id) === brandId);
        }
    },
    mounted() {
        this.fetchCategories();
        this.fetchBrands();
        this.fetchModels();
        this.fetchAvailableProducts();
        if (this.$route.params.id) {
            this.isEditing = true;
            this.fetchProduct(this.$route.params.id);
        }
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
        async onBrandChange() {
            // Reset model when brand changes
            this.form.model_id = null;
            // Optionally reload models for the selected brand
            if (this.form.brand_id) {
                await this.fetchModelsForBrand(this.form.brand_id);
            }
        },
        async fetchModelsForBrand(brandId) {
            try {
                const response = await apiClient.get(`/inventory/product-models?brand_id=${brandId}`);
                if (response.data.success) {
                    // Update models list with brand-specific models
                    // We'll merge with existing models to avoid losing other brands' models
                    const brandModels = response.data.data;
                    // Remove old models for this brand and add new ones
                    this.models = this.models.filter(m => parseInt(m.brand_id) !== parseInt(brandId));
                    this.models = [...this.models, ...brandModels];
                }
            } catch (err) {
                console.error('Error fetching models for brand:', err);
            }
        },
        async quickAddModel() {
            if (!this.newModelName.trim() || !this.form.brand_id) {
                toast.error('Lütfen model adını girin.');
                return;
            }

            try {
                const response = await apiClient.post('/inventory/product-models', {
                    brand_id: this.form.brand_id,
                    name: this.newModelName.trim()
                });

                if (response.data.success) {
                    const newModel = response.data.data;
                    this.models.push(newModel);
                    this.form.model_id = newModel.id;
                    this.showQuickAddModel = false;
                    this.newModelName = '';
                    toast.success('Model başarıyla eklendi.');
                }
            } catch (err) {
                console.error('Error adding model:', err);
                toast.error(err.response?.data?.message || 'Model eklenirken bir hata oluştu.');
            }
        },
        async fetchAvailableProducts() {
            this.error = null;
            try {
                const response = await apiClient.get('/inventory/products?all=true');
                if (response.data.success) {
                    this.availableProducts = response.data.data;
                }
            } catch (err) {
                console.error(err);
                this.error = 'Ürünler yüklenirken bir hata oluştu.';
            }
        },
        async fetchProduct(id) {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.get(`/inventory/products/${id}`);
                if (response.data.success) {
                    const product = response.data.data;
                    this.form = {
                        category_id: product.category_id || null,
                        brand_id: product.brand_id || null,
                        model_id: product.model_id || null,
                        name: product.name || '',
                        sku: product.sku || '',
                        barcode: product.barcode || '',
                        base_price: product.base_price || null,
                        cost_price: product.cost_price || null,
                        description: product.description || '',
                        is_active: !!product.is_active,
                        compatible_ids: []
                    };
                    
                    // Map objects to IDs for the select model
                    if (product.compatibles) {
                        this.form.compatible_ids = product.compatibles.map(p => p.id);
                    } else {
                        this.form.compatible_ids = [];
                    }
                    
                    // Remove self from available list to avoid self-reference loop in UI
                    this.availableProducts = this.availableProducts.filter(p => p.id !== parseInt(id));
                }
            } catch (err) {
                console.error(err);
                this.error = err.response?.data?.message || 'Ürün bilgileri yüklenirken hata oluştu.';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        },
        async saveProduct() {
            this.loading = true;
            this.error = null;
            try {
                // Prepare payload
                const payload = { ...this.form };
                delete payload.compatibles; // Don't send the objects back

                let response;
                if (this.isEditing) {
                    response = await apiClient.put(`/inventory/products/${this.$route.params.id}`, payload);
                    toast.success('Ürün başarıyla güncellendi.');
                } else {
                    response = await apiClient.post('/inventory/products', payload);
                    toast.success('Ürün başarıyla eklendi.');
                }

                if (response.data.success) {
                    this.$router.push('/products');
                }
            } catch (err) {
                console.error(err);
                if (err.response && err.response.data) {
                    if (err.response.data.message) {
                        this.error = err.response.data.message;
                    }
                    if (err.response.data.errors) {
                        // Very simple error display, could be improved
                        const errorMessages = Object.values(err.response.data.errors).flat();
                        this.error = this.error ? this.error + ': ' + errorMessages.join(', ') : errorMessages.join(', ');
                    }
                } else {
                    this.error = 'Bir hata oluştu.';
                }
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
