<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <h2 class="text-xl font-bold text-gray-800">Yeni İade / Geri Alım Oluştur</h2>
      <button @click="$router.push('/returns')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">Vazgeç</button>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Type Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">İşlem Türü</label>
                <div class="flex space-x-4">
                    <button type="button" 
                        @click="form.type = 'sale_return'; form.supplier_id = null;"
                        class="flex-1 py-3 px-4 border rounded-md text-center font-medium transition-colors"
                        :class="form.type === 'sale_return' ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                        Satış İadesi (Müşteriden Gelen)
                    </button>
                    <button type="button"
                        @click="form.type = 'purchase_return'; form.customer_id = null;" 
                        class="flex-1 py-3 px-4 border rounded-md text-center font-medium transition-colors"
                        :class="form.type === 'purchase_return' ? 'border-orange-600 bg-orange-50 text-orange-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                        Alış İadesi (Tedarikçiye Giden)
                    </button>
                </div>
            </div>

            <!-- Date -->
            <div>
                 <label class="block text-sm font-medium text-gray-700 mb-2">Tarih</label>
                 <input v-model="form.date" type="date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Contact Selection -->
            <div v-if="form.type === 'sale_return'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Müşteri Seçimi</label>
                <select v-model="form.customer_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    <option value="" disabled>Müşteri Seçin</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} {{ c.surname }} ({{ c.company_name }})</option>
                </select>
            </div>
             <div v-if="form.type === 'purchase_return'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tedarikçi Seçimi</label>
                <select v-model="form.supplier_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    <option value="" disabled>Tedarikçi Seçin</option>
                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                </select>
            </div>
            
            <div>
                 <label class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
                 <textarea v-model="form.notes" rows="1" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"></textarea>
            </div>
        </div>

        <!-- Items -->
        <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
             <h3 class="text-sm font-medium text-gray-900 mb-3 uppercase tracking-wide">İade Edilecek Ürünler</h3>
             <div v-for="(item, index) in form.items" :key="index" class="flex gap-4 mb-3 items-end">
                 <div class="flex-1">
                     <label class="block text-xs text-gray-500 mb-1" v-if="index===0">Ürün</label>
                     <select v-model="item.product_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                         <option value="" disabled>Seçiniz</option>
                         <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} (Stok: {{ p.stock_quantity }})</option>
                     </select>
                 </div>
                 <div class="w-32">
                     <label class="block text-xs text-gray-500 mb-1" v-if="index===0">Miktar</label>
                     <input v-model="item.quantity" type="number" step="0.01" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                 </div>
                 <div class="w-32">
                     <label class="block text-xs text-gray-500 mb-1" v-if="index===0">İade Birim Fiyatı</label>
                     <input v-model="item.price" type="number" step="0.01" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                 </div>
                 <div class="w-32 pt-6">
                     <div class="text-right font-medium text-gray-900">{{ formatCurrency(item.quantity * item.price) }}</div>
                 </div>
                 <button @click="removeItem(index)" class="text-red-500 hover:text-red-700 p-2">Sil</button>
             </div>
             
             <div class="flex justify-between items-center mt-2">
                 <button @click="addItem" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">+ Ürün Ekle</button>
                 <div class="text-lg font-bold text-gray-900">Toplam: {{ formatCurrency(totalAmount) }}</div>
             </div>
        </div>
        
        <div class="flex justify-end pt-4 border-t">
            <button @click="saveReturn" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-bold shadow-lg transform hover:-translate-y-0.5 transition-all">
                İadeyi Oluştur
            </button>
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
                type: 'sale_return',
                date: new Date().toISOString().substr(0, 10),
                customer_id: '',
                supplier_id: '',
                notes: '',
                items: [{ product_id: '', quantity: 1, price: 0 }]
            },
            customers: [],
            suppliers: [],
            products: []
        }
    },
    computed: {
        totalAmount() {
            return this.form.items.reduce((sum, item) => sum + (item.quantity * item.price), 0);
        }
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            try {
                // Fetch Customers
                const r1 = await axios.get('/api/customers');
                this.customers = r1.data.data;
                // Fetch Suppliers
                const r2 = await axios.get('/api/suppliers');
                this.suppliers = r2.data.data;
                // Fetch Products
                const r3 = await axios.get('/api/inventory/products');
                this.products = r3.data.data;
            } catch (error) {
                console.error('Initial data fetch error:', error);
            }
        },
        addItem() {
            this.form.items.push({ product_id: '', quantity: 1, price: 0 });
        },
        removeItem(index) {
            this.form.items.splice(index, 1);
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value || 0);
        },
        async saveReturn() {
            // Validation
            if (this.form.type === 'sale_return' && !this.form.customer_id) return alert('Lütfen müşteri seçin');
            if (this.form.type === 'purchase_return' && !this.form.supplier_id) return alert('Lütfen tedarikçi seçin');
            if (this.form.items.length === 0) return alert('En az bir ürün ekleyin');

            try {
                await axios.post('/api/inventory/returns', this.form);
                alert('İade talebi oluşturuldu.');
                this.$router.push('/returns');
            } catch (error) {
                console.error(error);
                alert('Hata: ' + (error.response?.data?.message || error.message));
            }
        }
    }
}
</script>
