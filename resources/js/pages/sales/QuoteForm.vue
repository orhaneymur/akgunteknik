<template>
    <div>
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate mb-6">
            Yeni Teklif Oluştur
        </h2>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Teklif Detayları</h3>
                <div class="space-y-4">
                    <div>
                        <label for="customer" class="block text-sm font-medium text-gray-700">Müşteri Seçimi</label>
                        <select id="customer" v-model="selectedCustomerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Müşteri Seçiniz (Zorunlu değil ama önerilir)</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                         <label for="valid_until" class="block text-sm font-medium text-gray-700">Geçerlilik Tarihi</label>
                         <input type="date" id="valid_until" v-model="validUntil" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    </div>

                    <div class="border-t pt-4 mt-4">
                        <label for="product" class="block text-sm font-medium text-gray-700">Ürün Ekle</label>
                        <select id="product" v-model="selectedProductId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="" disabled>Seçiniz</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }} ({{ product.sku }}) - {{ product.base_price }} ₺
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
                    <button @click="addToCart" :disabled="!selectedProductId || quantity < 1" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400">
                        Listeye Ekle
                    </button>
                </div>
            </div>

            <!-- Quote Summary -->
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Teklif Özeti</h3>
                <ul class="divide-y divide-gray-200">
                    <li v-for="(item, index) in cart" :key="index" class="py-4 flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ item.product.name }}</p>
                            <p class="text-sm text-gray-500">{{ item.quantity }} x {{ item.product.base_price }} ₺</p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-sm font-medium text-gray-900 mr-4">{{ (item.quantity * item.product.base_price).toFixed(2) }} ₺</p>
                            <button @click="removeFromCart(index)" class="text-red-600 hover:text-red-900 text-sm">Sil</button>
                        </div>
                    </li>
                    <li v-if="cart.length === 0" class="py-4 text-center text-gray-500">Liste boş.</li>
                </ul>
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex justify-between text-base font-medium text-gray-900">
                        <p>Toplam</p>
                        <p>{{ totalAmount }} ₺</p>
                    </div>
                    <button @click="saveQuote" :disabled="cart.length === 0" class="mt-6 w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:bg-gray-400">
                        Teklifi Kaydet
                    </button>
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
            customers: [],
            selectedProductId: '',
            selectedCustomerId: '',
            quantity: 1,
            validUntil: '',
            cart: []
        }
    },
    computed: {
        selectedProduct() {
            return this.products.find(p => p.id === this.selectedProductId);
        },
        totalAmount() {
            return this.cart.reduce((total, item) => total + (item.quantity * item.product.base_price), 0).toFixed(2);
        }
    },
    mounted() {
        this.fetchProducts();
        this.fetchCustomers();
        
        // Default valid until 30 days
         const date = new Date();
         date.setDate(date.getDate() + 30);
         this.validUntil = date.toISOString().split('T')[0];
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
            } catch (err) {
                console.error(err);
            }
        },
        async fetchProducts() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/inventory/products', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.products = response.data.data.filter(p => p.is_active);
                }
            } catch (err) {
                console.error(err);
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
                    quantity: this.quantity
                });
            }

            // Reset selection
            this.selectedProductId = '';
            this.quantity = 1;
        },
        removeFromCart(index) {
            this.cart.splice(index, 1);
        },
        async saveQuote() {
            try {
                const token = localStorage.getItem('token');
                const quoteItems = this.cart.map(item => ({
                    product_id: item.product.id,
                    quantity: item.quantity
                }));

                const response = await axios.post('/api/sales/quotes', {
                    items: quoteItems,
                    customer_id: this.selectedCustomerId || null,
                    valid_until: this.validUntil
                }, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                if (response.data.success) {
                    alert('Teklif başarıyla oluşturuldu!');
                    this.$router.push('/quotes');
                }
            } catch (err) {
                console.error(err);
                alert('Hata: ' + (err.response?.data?.message || err.message));
            }
        }
    }
}
</script>
