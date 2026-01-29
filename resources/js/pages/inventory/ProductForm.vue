<template>
    <div>
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">{{ isEditing ? 'Ürün Düzenle' : 'Yeni Ürün' }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ isEditing ? 'Mevcut ürün bilgilerini güncelleyin.' : 'Stok takibi yapılacak yeni bir ürün tanımlayın.' }}
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form @submit.prevent="saveProduct">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-4">
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

                        <div v-if="error" class="px-4 py-3 bg-red-50 text-right sm:px-6 text-red-600 text-sm">
                            {{ error }}
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <button type="button" @click="$router.push('/products')" class="mr-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                İptal
                            </button>
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
import axios from 'axios';

export default {
    data() {
        return {
            form: {
                name: '',
                sku: '',
                barcode: '',
                base_price: null,
                cost_price: null,
                description: '',
                is_active: true,
                compatible_ids: []
            },
            availableProducts: [],
            error: null,
            isEditing: false
        }
    },
    mounted() {
        this.fetchAvailableProducts();
        if (this.$route.params.id) {
            this.isEditing = true;
            this.fetchProduct(this.$route.params.id);
        }
    },
    methods: {
        async fetchAvailableProducts() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/inventory/products?all=true', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    // Filter out current product if editing (done in computed or just allow self-selection check later)
                    // For simplicity, just load all. 
                    this.availableProducts = response.data.data;
                }
            } catch (err) {
                console.error(err);
            }
        },
        async fetchProduct(id) {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get(`/api/inventory/products/${id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.form = response.data.data;
                    this.form.is_active = !!this.form.is_active; 
                    // Map objects to IDs for the select model
                    if (this.form.compatibles) {
                        this.form.compatible_ids = this.form.compatibles.map(p => p.id);
                    } else {
                        this.form.compatible_ids = [];
                    }
                    
                    // Remove self from available list to avoid self-reference loop in UI
                    this.availableProducts = this.availableProducts.filter(p => p.id !== parseInt(id));
                }
            } catch (err) {
                console.error(err);
                alert('Ürün bilgileri yüklenirken hata oluştu: ' + (err.response?.data?.message || err.message));
                // this.$router.push('/products'); // Debugging: Disable redirect to see error
            }
        },
        async saveProduct() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: `Bearer ${token}` };
                let response;

                // Prepare payload
                const payload = { ...this.form };
                delete payload.compatibles; // Don't send the objects back

                if (this.isEditing) {
                    response = await axios.put(`/api/inventory/products/${this.$route.params.id}`, payload, { headers });
                } else {
                    response = await axios.post('/api/inventory/products', payload, { headers });
                }

                if (response.data.success) {
                    this.$router.push('/products');
                }
            } catch (err) {
                console.error(err);
                if (err.response && err.response.data && err.response.data.message) {
                    this.error = err.response.data.message;
                    if (err.response.data.errors) {
                        // Very simple error display, could be improved
                        this.error += ': ' + Object.values(err.response.data.errors).flat().join(', ');
                    }
                } else {
                    this.error = 'Bir hata oluştu.';
                }
            }
        }
    }
}
</script>
