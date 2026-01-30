<template>
    <div>
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate mb-6">
            Yeni Satış Oluştur
        </h2>

        <ErrorAlert :error="error" @dismiss="error = null" />
        <LoadingSpinner :show="loading" />
        
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Sipariş Detayları</h3>
                <div class="space-y-4">
                    <div>
                        <label for="customer" class="block text-sm font-medium text-gray-700">Müşteri Seçimi</label>
                        <select id="customer" v-model="selectedCustomerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="" disabled>Müşteri Seçiniz (Opsiyonel)</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="product" class="block text-sm font-medium text-gray-700">Ürün</label>
                        <select id="product" v-model="selectedProductId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="" disabled>Seçiniz</option>
                            <option v-for="product in products" :key="product.id" :value="product.id" :class="{'text-red-500': !product.available_stock || product.available_stock <= 0}">
                                {{ product.name }} ({{ product.sku }}) - {{ product.base_price }} ₺ - Stok: {{ product.available_stock || 0 }} {{ (!product.available_stock || product.available_stock <= 0) ? '(Stok Yok)' : '' }}
                            </option>
                        </select>
                        <div v-if="selectedProduct && selectedProduct.compatibles && selectedProduct.compatibles.length > 0" class="mt-2 p-2 bg-yellow-50 rounded border border-yellow-200">
                            <p class="text-sm font-medium text-yellow-800">Muadil / Uyumlu Ürünler:</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <button type="button" v-for="comp in selectedProduct.compatibles" :key="comp.id" @click="selectedProductId = comp.id" 
                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    {{ comp.name }} ({{ comp.base_price }} ₺) -> Seç
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Adet</label>
                        <input type="number" id="quantity" v-model.number="quantity" min="1" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div v-if="selectedProduct && selectedProduct.available_stock <= 0" class="text-red-600 text-sm mt-1">
                        Bu ürün stokta yok!
                    </div>
                    <button @click="addToCart" :disabled="!selectedProductId || quantity < 1 || (selectedProduct && quantity > selectedProduct.available_stock)" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400">
                        Sepete Ekle
                    </button>
                </div>
            </div>

            <!-- Cart / Order Summary -->
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Sepet Özeti</h3>
                <ul class="divide-y divide-gray-200">
                    <li v-for="(item, index) in cart" :key="index" class="py-4 flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ item.product.name }}</p>
                            <p class="text-sm text-gray-500">Miktar: {{ item.quantity }}</p>
                            <div class="flex items-center mt-1">
                                <span class="text-xs text-gray-500 mr-2">Birim Fiyat:</span>
                                <input type="number" v-model.number="item.price" min="0" step="0.01" class="w-24 text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-1">
                                <span class="ml-1 text-sm text-gray-700">₺</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <p class="text-sm font-medium text-gray-900 mb-2">{{ (item.quantity * item.price).toFixed(2) }} ₺</p>
                            <button @click="removeFromCart(index)" class="text-red-600 hover:text-red-900 text-sm">Sil</button>
                        </div>
                    </li>
                    <li v-if="cart.length === 0" class="py-4 text-center text-gray-500">Sepetiniz boş.</li>
                </ul>
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex justify-between text-base font-medium text-gray-900">
                        <p>Toplam</p>
                        <p>{{ totalAmount }} ₺</p>
                    </div>
                    <button @click="completeSale" :disabled="cart.length === 0" class="mt-6 w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:bg-gray-400">
                        Satışı Tamamla
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import apiClient from '../../api/client.js';
import ErrorAlert from '../../Components/ErrorAlert.vue';
import LoadingSpinner from '../../Components/LoadingSpinner.vue';
import toast from '../../utils/toast.js';

export default {
    components: {
        ErrorAlert,
        LoadingSpinner
    },
    data() {
        return {
            products: [],
            customers: [],
            selectedProductId: '',
            selectedCustomerId: '',
            quantity: 1,
            cart: [],
            loading: false,
            error: null
        }
    },
    computed: {
        selectedProduct() {
            return this.products.find(p => p.id === this.selectedProductId);
        },
        totalAmount() {
            return this.cart.reduce((total, item) => total + (item.quantity * item.price), 0).toFixed(2);
        }
    },
    mounted() {
        this.fetchProducts();
        this.fetchCustomers();
    },
    methods: {
        async fetchCustomers() {
            this.error = null;
            try {
                const response = await apiClient.get('/customers/customers?all=true');
                if (response.data.success) {
                    this.customers = Array.isArray(response.data.data) ? response.data.data : [];
                }
            } catch (err) {
                console.error('Müşteri yükleme hatası:', err);
                this.error = 'Müşteriler yüklenirken bir hata oluştu.';
                toast.error('Müşteriler yüklenemedi.');
            }
        },
        async fetchProducts() {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.get('/inventory/products?all=true');
                if (response.data.success) {
                    // Only show active products
                    this.products = Array.isArray(response.data.data) 
                        ? response.data.data.filter(p => p.is_active)
                        : [];
                    
                    // Products already have compatibles and available_stock from API
                    // Ensure available_stock is set (fallback to 0 if not)
                    this.products.forEach(product => {
                        if (!product.available_stock && product.available_stock !== 0) {
                            product.available_stock = product.current_stock || 0;
                        }
                        if (!product.compatibles) {
                            product.compatibles = [];
                        }
                    });
                }
            } catch (err) {
                console.error('Ürün yükleme hatası:', err);
                this.error = 'Ürünler yüklenirken bir hata oluştu.';
                toast.error('Ürünler yüklenemedi.');
            } finally {
                this.loading = false;
            }
        },
        addToCart() {
            const product = this.products.find(p => p.id === this.selectedProductId);
            if (!product) return;

            const existingItem = this.cart.find(item => item.product.id === product.id);
            if (existingItem) {
                existingItem.quantity += this.quantity;
            } else {
                this.cart.push({
                    product: product,
                    quantity: this.quantity,
                    price: product.base_price // Initialize with base price
                });
            }

            // Reset selection
            this.selectedProductId = '';
            this.quantity = 1;
        },
        removeFromCart(index) {
            this.cart.splice(index, 1);
        },
        async completeSale() {
            this.loading = true;
            this.error = null;
            try {
                const orderItems = this.cart.map(item => ({
                    product_id: item.product.id,
                    quantity: item.quantity,
                    unit_price: item.price // Send custom price
                }));

                const response = await apiClient.post('/sales/orders', {
                    items: orderItems,
                    customer_id: this.selectedCustomerId || null
                });

                if (response.data.success) {
                    toast.success('Satış başarıyla tamamlandı!');
                    this.cart = [];
                    this.selectedCustomerId = '';
                    this.selectedProductId = '';
                    this.quantity = 1;
                    // Optionally redirect to orders list
                    this.$router.push('/sales');
                }
            } catch (err) {
                console.error('Satış hatası:', err);
                const errorMsg = err.response?.data?.message || 'Satış sırasında bir hata oluştu.';
                this.error = errorMsg;
                toast.error(errorMsg);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
