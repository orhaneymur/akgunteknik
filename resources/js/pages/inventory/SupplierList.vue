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

        <!-- Search Bar -->
        <div class="mb-4">
            <div class="relative">
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="debounceSearch"
                    placeholder="Tedarikçi adı, email, telefon veya yetkili kişi ile ara..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <ErrorAlert :error="error" @dismiss="error = null" />
        <LoadingSpinner :show="loading" />

        <div class="flex flex-col" v-if="!loading">
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
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">{{ searchQuery ? 'Arama sonucu bulunamadı.' : 'Henüz tedarikçi eklenmemiş.' }}</td>
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
                        Toplam <span class="font-medium">{{ pagination.total }}</span> tedarikçiden <span class="font-medium">{{ pagination.from }}</span> ile <span class="font-medium">{{ pagination.to }}</span> arası gösteriliyor.
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
import toast from '../../utils/toast.js';
import ErrorAlert from '../../Components/ErrorAlert.vue';
import LoadingSpinner from '../../Components/LoadingSpinner.vue';
import PaymentModal from '../../components/PaymentModal.vue';

export default {
    components: { 
        PaymentModal,
        ErrorAlert,
        LoadingSpinner
    },
    data() {
        return {
            suppliers: [],
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
        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.current_page = 1;
                this.fetchSuppliers();
            }, 500);
        },
        async fetchSuppliers(page = 1) {
            this.loading = true;
            this.error = null;
            try {
                const params = { page };
                if (this.searchQuery) {
                    params.search = this.searchQuery;
                }
                const response = await apiClient.get('/inventory/suppliers', { params });
                if (response.data.success) {
                    if (response.data.data.data) {
                        // Paginated response
                        this.suppliers = response.data.data.data;
                        this.pagination = response.data.data;
                    } else {
                        // Non-paginated response (backward compatibility)
                        this.suppliers = response.data.data;
                        this.pagination = {
                            current_page: 1,
                            last_page: 1,
                            total: this.suppliers.length,
                            from: 1,
                            to: this.suppliers.length
                        };
                    }
                }
            } catch (error) {
                console.error(error);
                this.error = error.response?.data?.message || 'Tedarikçiler yüklenirken bir hata oluştu.';
            } finally {
                this.loading = false;
            }
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.fetchSuppliers(page);
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
            this.error = null;
            try {
                if (this.isEdit) {
                    await apiClient.put(`/inventory/suppliers/${this.form.id}`, this.form);
                    toast.success('Tedarikçi başarıyla güncellendi.');
                } else {
                    await apiClient.post('/inventory/suppliers', this.form);
                    toast.success('Tedarikçi başarıyla eklendi.');
                }
                this.closeModal();
                this.fetchSuppliers(this.pagination.current_page);
            } catch (error) {
                console.error(error);
                this.error = error.response?.data?.message || 'Kaydetme başarısız.';
                toast.error(this.error);
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
