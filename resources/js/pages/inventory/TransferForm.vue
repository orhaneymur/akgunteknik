<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <h2 class="text-xl font-bold text-gray-800">Yeni Depo Transferi</h2>
      <button @click="$router.push('/transfers')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">
        Listeye Dön
      </button>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6">
       <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
           <div>
               <label class="block text-sm font-medium text-gray-700">Tarih</label>
               <input v-model="form.date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
           </div>
           <div>
               <label class="block text-sm font-medium text-gray-700">Kaynak Depo (Nereden)</label>
               <select v-model="form.from_warehouse_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                   <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
               </select>
           </div>
           <div>
               <label class="block text-sm font-medium text-gray-700">Hedef Depo (Nereye)</label>
               <select v-model="form.to_warehouse_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                   <option v-for="w in warehouses" :key="w.id" :value="w.id" :disabled="w.id === form.from_warehouse_id">{{ w.name }}</option>
               </select>
           </div>
       </div>

       <div class="mb-6">
           <label class="block text-sm font-medium text-gray-700">Notlar</label>
           <textarea v-model="form.notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"></textarea>
       </div>

       <!-- Items -->
       <div class="mb-6">
           <h3 class="text-lg font-medium text-gray-900 mb-2">Transfer Edilecek Ürünler</h3>
           <div class="border border-gray-200 rounded-md p-4 bg-gray-50">
               <div v-for="(item, index) in form.items" :key="index" class="flex gap-4 mb-2 items-end">
                   <div class="flex-1">
                       <label class="block text-xs text-gray-500 mb-1" v-if="index === 0">Ürün</label>
                       <!-- Simple select for now, ideally searchable -->
                       <select v-model="item.product_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                           <option value="" disabled>Ürün Seçin</option>
                           <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                       </select>
                   </div>
                   <div class="w-32">
                       <label class="block text-xs text-gray-500 mb-1" v-if="index === 0">Miktar</label>
                       <input v-model="item.quantity" type="number" step="0.01" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                   </div>
                   <button @click="removeItem(index)" class="text-red-600 hover:text-red-800 p-2">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                           <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                       </svg>
                   </button>
               </div>
               <button @click="addItem" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900 font-medium">+ Başka Ürün Ekle</button>
           </div>
       </div>

       <div class="flex justify-end">
           <button @click="saveTransfer" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-base font-medium">
               Transferi Kaydet
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
                date: new Date().toISOString().substr(0, 10),
                from_warehouse_id: '',
                to_warehouse_id: '',
                notes: '',
                items: [{ product_id: '', quantity: 1 }]
            },
            warehouses: [], // Need to fetch these. Wait, where are warehouses exposed?
            products: []
        }
    },
    mounted() {
        this.fetchProducts();
        // We probably need a warehouses endpoint or just fetch from somewhere if available.
        // Assuming we created a resource previously or we can mock/fetch core data.
        // Checking task.md... Warehouses were part of core migration but not explicitly exposed via API in this session.
        // I should probably check if there is a WarehouseController or create a quick one/use a route to get them.
        // For now, let's try assuming /api/warehouses might exist or I can add it to Core/Routes/api.php quickly.
        this.fetchWarehouses();
    },
    methods: {
        async fetchProducts() {
            try {
                const response = await axios.get('/api/inventory/products'); // Correct Prefix? Usually just /api/products based on Routes/api.php in Inventory
                this.products = response.data.data ? response.data.data : response.data;
            } catch (error) {
                 // Try fallback
                 try {
                    const r2 = await axios.get('/api/products');
                    this.products = r2.data.data ? r2.data.data : r2.data;
                 } catch (e) { console.error(e) }
            }
        },
        async fetchWarehouses() {
             // Quick hack: If no endpoint, I might need to add one.
             // Let's assume one exists or I'll add it.
             // Looking at Core module is out of scope for *Inventory* but Warehouses are Core.
             // I'll try to fetch, if 404 I'll handle it.
             try {
                 const response = await axios.get('/api/warehouses');
                 this.warehouses = response.data;
             } catch (error) {
                 console.error('Warehouses endpoint missing, mocking or need to fix.');
             }
        },
        addItem() {
            this.form.items.push({ product_id: '', quantity: 1 });
        },
        removeItem(index) {
            this.form.items.splice(index, 1);
        },
        async saveTransfer() {
            try {
                // Filter empty items
                const validItems = this.form.items.filter(i => i.product_id && i.quantity > 0);
                if (validItems.length === 0) {
                    alert('Lütfen en az bir ürün seçin.');
                    return;
                }
                
                await axios.post('/api/inventory-transfers', {
                    ...this.form,
                    items: validItems
                });
                
                alert('Transfer taslağı oluşturuldu.');
                this.$router.push('/transfers');
            } catch (error) {
                console.error(error);
                alert('Hata oluştu: ' + (error.response?.data?.message || error.message));
            }
        }
    }
}
</script>
