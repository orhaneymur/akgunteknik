<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold leading-tight bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                    Ürün Modelleri
                </h2>
                <p class="mt-1 text-sm text-gray-500">Ürün modellerinizi yönetin</p>
            </div>
            <button @click="showForm = true; editingModel = null" 
                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Model Ekle
            </button>
        </div>

        <!-- Filter by Brand -->
        <div class="mb-4">
            <label for="filter_brand" class="block text-sm font-medium text-gray-700 mb-1">Markaya Göre Filtrele</label>
            <select id="filter_brand" v-model="filterBrandId" @change="fetchModels"
                class="block w-full md:w-64 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                <option value="">Tüm Markalar</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                    {{ brand.name }}
                </option>
            </select>
        </div>

        <ErrorAlert :error="error" @dismiss="error = null" />
        <LoadingSpinner :show="loading" />

        <!-- Model Form Modal -->
        <div v-if="showForm" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 transition-opacity" @click.self="closeForm">
            <div class="relative top-20 mx-auto p-6 border-0 w-96 shadow-2xl rounded-xl bg-white/95 backdrop-blur-xl">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingModel ? 'Model Düzenle' : 'Yeni Model Ekle' }}
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marka *</label>
                        <select v-model="form.brand_id" :disabled="!!editingModel"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"
                            :class="{'bg-gray-100': !!editingModel}">
                            <option :value="null">Marka Seçiniz</option>
                            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                {{ brand.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model Adı *</label>
                        <input type="text" v-model="form.name" placeholder="Örn: Galaxy S21, iPhone 13"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kod (Opsiyonel)</label>
                        <input type="text" v-model="form.code" placeholder="Örn: GALAXY-S21, IPHONE-13"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                        <textarea v-model="form.description" rows="3"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" v-model="form.is_active" id="is_active"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktif</label>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeForm"
                            class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition-all duration-200">
                            İptal
                        </button>
                        <button type="button" @click="saveModel"
                            class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                            Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteConfirm"
            title="Model Silme Onayı"
            message="Bu modeli silmek istediğinizden emin misiniz? Bu modele ait ürünler varsa silme işlemi başarısız olacaktır."
            type="danger"
            confirm-text="Evet, Sil"
            cancel-text="İptal"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />

        <!-- Models Table -->
        <div class="flex flex-col" v-if="!loading">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200/50">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Marka
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Model Adı
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kod
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Açıklama
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Durum
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">İşlemler</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="model in models" :key="model.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ model.brand?.name || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ model.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ model.code || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ model.description || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="model.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ model.is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="editModel(model)" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors mr-2">Düzenle</button>
                                        <button @click="deleteModel(model.id)" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">Sil</button>
                                    </td>
                                </tr>
                                <tr v-if="models.length === 0">
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ filterBrandId ? 'Bu marka için henüz model bulunmuyor.' : 'Henüz model bulunmuyor.' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
            models: [],
            brands: [],
            loading: false,
            error: null,
            showForm: false,
            editingModel: null,
            filterBrandId: '',
            form: {
                brand_id: null,
                name: '',
                code: '',
                description: '',
                is_active: true
            },
            showDeleteConfirm: false,
            modelToDelete: null
        }
    },
    mounted() {
        this.fetchBrands();
        this.fetchModels();
    },
    methods: {
        async fetchBrands() {
            try {
                const response = await apiClient.get('/inventory/brands');
                if (response.data.success) {
                    this.brands = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching brands:', error);
            }
        },
        async fetchModels() {
            this.loading = true;
            this.error = null;
            try {
                const params = {};
                if (this.filterBrandId) {
                    params.brand_id = this.filterBrandId;
                }
                const response = await apiClient.get('/inventory/product-models', { params });
                if (response.data.success) {
                    this.models = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching models:', error);
                this.error = error.response?.data?.message || 'Modeller yüklenirken bir hata oluştu.';
            } finally {
                this.loading = false;
            }
        },
        editModel(model) {
            this.editingModel = model;
            this.form = {
                brand_id: model.brand_id,
                name: model.name,
                code: model.code || '',
                description: model.description || '',
                is_active: model.is_active
            };
            this.showForm = true;
        },
        closeForm() {
            this.showForm = false;
            this.editingModel = null;
            this.form = {
                brand_id: null,
                name: '',
                code: '',
                description: '',
                is_active: true
            };
        },
        async saveModel() {
            if (!this.form.brand_id) {
                toast.error('Marka seçimi gereklidir.');
                return;
            }
            if (!this.form.name.trim()) {
                toast.error('Model adı gereklidir.');
                return;
            }

            this.loading = true;
            this.error = null;
            try {
                if (this.editingModel) {
                    await apiClient.put(`/inventory/product-models/${this.editingModel.id}`, this.form);
                    toast.success('Model başarıyla güncellendi.');
                } else {
                    await apiClient.post('/inventory/product-models', this.form);
                    toast.success('Model başarıyla eklendi.');
                }
                this.closeForm();
                this.fetchModels();
            } catch (error) {
                console.error('Error saving model:', error);
                this.error = error.response?.data?.message || 'Model kaydedilirken bir hata oluştu.';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        },
        deleteModel(id) {
            this.modelToDelete = id;
            this.showDeleteConfirm = true;
        },
        async confirmDelete() {
            if (!this.modelToDelete) return;
            
            this.error = null;
            try {
                await apiClient.delete(`/inventory/product-models/${this.modelToDelete}`);
                this.fetchModels();
                toast.success('Model başarıyla silindi.');
            } catch (error) {
                console.error('Error deleting model:', error);
                this.error = error.response?.data?.message || 'Model silinirken bir hata oluştu.';
                toast.error(this.error);
            } finally {
                this.showDeleteConfirm = false;
                this.modelToDelete = null;
            }
        },
        cancelDelete() {
            this.showDeleteConfirm = false;
            this.modelToDelete = null;
        }
    }
}
</script>
