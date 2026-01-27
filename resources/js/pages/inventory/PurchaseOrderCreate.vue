<template>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-6">Yeni Alış Siparişi Oluştur</h3>
        
        <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Order Details -->
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Sipariş Bilgileri</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tedarikçi</label>
                            <select v-model="form.supplier_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm sm:text-sm">
                                <option value="" disabled>Seçiniz</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Depo / Şube</label>
                            <select v-model="form.warehouse_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm sm:text-sm">
                                <option value="" disabled>Seçiniz</option>
                                <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Add Items -->
                 <div class="bg-white shadow sm:rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Ürün Ekle</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ürün</label>
                            <select v-model="currentItem.product_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm sm:text-sm">
                                <option value="" disabled>Ürün Seçiniz</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Miktar</label>
                                <input type="number" v-model.number="currentItem.quantity" min="1" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Birim Maliyet (TL)</label>
                                <input type="number" v-model.number="currentItem.unit_cost" min="0" step="0.01" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                         <button type="button" @click="addItem" :disabled="!isItemValid" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                            Listeye Ekle
                        </button>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mt-8 bg-white shadow sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ürün</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Miktar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Birim Maliyet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Toplam</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(item, index) in form.items" :key="index">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ getProductName(item.product_id) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ item.quantity }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatCurrency(item.unit_cost) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatCurrency(item.quantity * item.unit_cost) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">Sil</button>
                            </td>
                        </tr>
                         <tr v-if="form.items.length === 0">
                             <td colspan="5" class="px-6 py-4 text-center text-gray-500">Listed ürün yok.</td>
                        </tr>
                    </tbody>
                     <tfoot class="bg-gray-50" v-if="form.items.length > 0">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Genel Toplam:</td>
                            <td class="px-6 py-3 text-left text-sm font-bold text-gray-900">{{ formatCurrency(grandTotal) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                 <router-link to="/purchase-orders" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    İptal
                </router-link>
                <button type="submit" :disabled="form.items.length === 0" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                    Siparişi Oluştur
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            form: {
                supplier_id: '',
                warehouse_id: '',
                items: []
            },
            currentItem: {
                product_id: '',
                quantity: 1,
                unit_cost: 0
            },
            suppliers: [],
            warehouses: [],
            products: []
        };
    },
    computed: {
        isItemValid() {
            return this.currentItem.product_id && this.currentItem.quantity > 0 && this.currentItem.unit_cost >= 0;
        },
        grandTotal() {
            return this.form.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost), 0);
        }
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            try {
                const token = localStorage.getItem('token');
                const headers = { Authorization: `Bearer ${token}` };
                
                const [suppliersRes, warehousesRes, productsRes] = await Promise.all([
                    axios.get('/api/inventory/suppliers', { headers }),
                    axios.get('/api/core/warehouses', { headers }),
                    axios.get('/api/inventory/products', { headers })
                ]);

                if (suppliersRes.data.success) this.suppliers = suppliersRes.data.data;
                if (warehousesRes.data.success) this.warehouses = warehousesRes.data.data;
                if (productsRes.data.success) this.products = productsRes.data.data;

                // Set default warehouse if available
                if (this.warehouses.length > 0) this.form.warehouse_id = this.warehouses[0].id;

            } catch (error) {
                console.error(error);
            }
        },
        addItem() {
            this.form.items.push({ ...this.currentItem });
            this.currentItem = { product_id: '', quantity: 1, unit_cost: 0 };
        },
        removeItem(index) {
            this.form.items.splice(index, 1);
        },
        getProductName(id) {
            const product = this.products.find(p => p.id === id);
            return product ? product.name : 'Bilinmeyen Ürün';
        },
         formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
        },
         async submitForm() {
            try {
                const token = localStorage.getItem('token');
                await axios.post('/api/inventory/purchase-orders', this.form, {
                     headers: { Authorization: `Bearer ${token}` }
                });
                alert('Alış siparişi oluşturuldu.');
                this.$router.push('/purchase-orders');
            } catch (error) {
                console.error(error);
                alert('Sipariş oluşturulamadı.');
            }
        }
    }
}
</script>
