<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Müşteriler
            </h2>
            <router-link to="/customers/create" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Yeni Müşteri Ekle
            </router-link>
        </div>

        <!-- Search Bar -->
        <div class="mb-4">
            <div class="relative">
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="debounceSearch"
                    placeholder="Müşteri adı, email, telefon veya vergi no ile ara..."
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

        <ConfirmationModal
            :show="showDeleteConfirm"
            title="Müşteri Silme Onayı"
            message="Bu müşteriyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz."
            type="danger"
            confirm-text="Evet, Sil"
            cancel-text="İptal"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />

        <div class="flex flex-col" v-if="!loading">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        İsim
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        İletişim
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Bakiye
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Düzenle</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="customer in customers" :key="customer.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                                        <div class="text-sm text-gray-500">{{ customer.tax_number ? 'VKN: ' + customer.tax_number : '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ customer.phone }}</div>
                                        <div class="text-sm text-gray-500">{{ customer.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                            :class="customer.current_balance < 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                                            {{ customer.current_balance }} ₺
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <router-link :to="{ name: 'CustomerEdit', params: { id: customer.id } }" class="text-indigo-600 hover:text-indigo-900 mr-3">Düzenle</router-link>
                                        <router-link :to="{ name: 'CustomerStatement', params: { id: customer.id } }" class="text-green-600 hover:text-green-900 mr-3">Ekstre</router-link>
                                        <button @click="deleteCustomer(customer.id)" class="text-red-600 hover:text-red-900">Sil</button>
                                    </td>
                                </tr>
                                <tr v-if="customers.length === 0">
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ searchQuery ? 'Arama sonucu bulunamadı.' : 'Henüz müşteri kaydı yok.' }}
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
                        Toplam <span class="font-medium">{{ pagination.total }}</span> müşteriden <span class="font-medium">{{ pagination.from }}</span> ile <span class="font-medium">{{ pagination.to }}</span> arası gösteriliyor.
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
            customers: [],
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
            customerToDelete: null
        }
    },
    mounted() {
        this.fetchCustomers();
    },
    methods: {
        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.current_page = 1;
                this.fetchCustomers();
            }, 500);
        },
        async fetchCustomers(page = 1) {
            this.loading = true;
            this.error = null;
            try {
                const params = { page };
                if (this.searchQuery) {
                    params.search = this.searchQuery;
                }
                const response = await apiClient.get('/customers/customers', { params });
                if (response.data.success) {
                    if (response.data.data.data) {
                        // Paginated response
                        this.customers = response.data.data.data;
                        this.pagination = response.data.data;
                    } else {
                        // Non-paginated response (backward compatibility)
                        this.customers = response.data.data;
                        this.pagination = {
                            current_page: 1,
                            last_page: 1,
                            total: this.customers.length,
                            from: 1,
                            to: this.customers.length
                        };
                    }
                }
            } catch (error) {
                console.error('Error fetching customers:', error);
                this.error = error.response?.data?.message || 'Müşteriler yüklenirken bir hata oluştu.';
            } finally {
                this.loading = false;
            }
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.fetchCustomers(page);
            }
        },
        deleteCustomer(id) {
            this.customerToDelete = id;
            this.showDeleteConfirm = true;
        },
        async confirmDelete() {
            if (!this.customerToDelete) return;
            
            this.error = null;
            try {
                await apiClient.delete(`/customers/customers/${this.customerToDelete}`);
                toast.success('Müşteri başarıyla silindi.');
                this.fetchCustomers(this.pagination.current_page);
            } catch (error) {
                console.error('Error deleting customer:', error);
                this.error = error.response?.data?.message || 'Silme işlemi başarısız oldu.';
                toast.error(this.error);
            } finally {
                this.showDeleteConfirm = false;
                this.customerToDelete = null;
            }
        },
        cancelDelete() {
            this.showDeleteConfirm = false;
            this.customerToDelete = null;
        }
    }
}
</script>
