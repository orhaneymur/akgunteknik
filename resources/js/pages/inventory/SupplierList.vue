<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Tedarikçiler
            </h2>
            <div class="flex">
                <button @click="openCreateModal" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Yeni Tedarikçi Ekle
                </button>
            </div>
        </div>

        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Firma Adı
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Yetkili
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    İletişim
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bakiye (Alacaklı)
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="supplier in suppliers" :key="supplier.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ supplier.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ supplier.contact_name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ supplier.email }}</div>
                                    <div class="text-xs">{{ supplier.phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">
                                    {{ formatCurrency(supplier.balance) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openPaymentModal(supplier)" class="text-green-600 hover:text-green-900 mr-4">Ödeme Yap</button>
                                    <button @click="viewStatement(supplier)" class="text-blue-600 hover:text-blue-900 mr-4">Cari Detay</button>
                                    <button @click="editSupplier(supplier)" class="text-indigo-600 hover:text-indigo-900">Düzenle</button>
                                </td>
                            </tr>
                             <tr v-if="suppliers.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">Henüz tedarikçi eklenmemiş.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ isEdit ? 'Tedarikçi Düzenle' : 'Yeni Tedarikçi' }}
                                </h3>
                                <div class="mt-2 space-y-4">
                                     <div>
                                        <label class="block text-sm font-medium text-gray-700">Firma Adı</label>
                                        <input type="text" v-model="form.name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Yetkili Kişi</label>
                                        <input type="text" v-model="form.contact_name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" v-model="form.email" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                     <div>
                                        <label class="block text-sm font-medium text-gray-700">Telefon</label>
                                        <input type="text" v-model="form.phone" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="saveSupplier" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Kaydet
                        </button>
                        <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            İptal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <PaymentModal 
            v-if="showPaymentModal" 
            :show="showPaymentModal" 
            :type="'payment'" 
            :payable-type="'supplier'" 
            :payable-id="selectedSupplier.id" 
            :entity-name="selectedSupplier.name"
            @close="closePaymentModal"
            @saved="fetchSuppliers"
        />
    </div>
</template>

<script>
import axios from 'axios';
import PaymentModal from '../../components/PaymentModal.vue';

export default {
    components: { PaymentModal },
    data() {
        return {
            suppliers: [],
            showModal: false,
            isEdit: false,
            form: {
                id: null,
                name: '',
                contact_name: '',
                email: '',
                phone: '',
                address: ''
            },
            showPaymentModal: false,
            selectedSupplier: null
        };
    },
    mounted() {
        this.fetchSuppliers();
    },
    methods: {
        async fetchSuppliers() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/inventory/suppliers', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.suppliers = response.data.data;
                }
            } catch (error) {
                console.error(error);
            }
        },
        openCreateModal() {
            this.resetForm();
            this.isEdit = false;
            this.showModal = true;
        },
        editSupplier(supplier) {
            this.form = { ...supplier };
            this.isEdit = true;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.resetForm();
        },
        resetForm() {
            this.form = {
                id: null,
                name: '',
                contact_name: '',
                email: '',
                phone: '',
                address: ''
            };
        },
        async saveSupplier() {
            try {
                const token = localStorage.getItem('token');
                if (this.isEdit) {
                    await axios.put(`/api/inventory/suppliers/${this.form.id}`, this.form, {
                        headers: { Authorization: `Bearer ${token}` }
                    });
                } else {
                    await axios.post('/api/inventory/suppliers', this.form, {
                        headers: { Authorization: `Bearer ${token}` }
                    });
                }
                this.closeModal();
                this.fetchSuppliers();
            } catch (error) {
                console.error(error);
                alert('Kaydetme başarısız.');
            }
        },
        openPaymentModal(supplier) {
            this.selectedSupplier = supplier;
            this.showPaymentModal = true;
        },
        closePaymentModal() {
            this.showPaymentModal = false;
            this.selectedSupplier = null;
        },
        viewStatement(supplier) {
            this.$router.push({ name: 'SupplierStatement', params: { id: supplier.id } });
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value || 0);
        }
    }
}
</script>
