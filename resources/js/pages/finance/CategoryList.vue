<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Gider Kategorileri</h2>
        <p class="text-sm text-gray-500">Maliyetleri takip etmek için kategoriler oluşturun</p>
      </div>
      <button @click="openModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
        + Yeni Kategori
      </button>
    </div>

    <!-- Category List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori Adı</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etiket Rengi</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <span :style="{ backgroundColor: category.color }" class="w-3 h-3 rounded-full mr-2"></span>
                <span class="text-sm font-medium text-gray-900">{{ category.name }}</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-2">
                    <div :style="{ backgroundColor: category.color }" class="w-6 h-6 rounded border border-gray-200"></div>
                    <span class="text-xs text-gray-500">{{ category.color }}</span>
                </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-500">{{ category.description || '-' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="editCategory(category)" class="text-indigo-600 hover:text-indigo-900 mr-3">Düzenle</button>
              <button @click="deleteCategory(category.id)" class="text-red-600 hover:text-red-900">Sil</button>
            </td>
          </tr>
          <tr v-if="categories.length === 0">
              <td colspan="4" class="px-6 py-4 text-center text-gray-500">Kayıtlı kategori bulunamadı.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              {{ isEdit ? 'Kategori Düzenle' : 'Yeni Kategori' }}
            </h3>
            <div class="mt-4 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Kategori Adı</label>
                <input v-model="form.name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Renk</label>
                <div class="flex space-x-2 mt-1">
                    <button v-for="color in colors" :key="color" 
                        @click="form.color = color"
                        :class="{'ring-2 ring-offset-2 ring-indigo-500': form.color === color}"
                        :style="{ backgroundColor: color }"
                        class="w-8 h-8 rounded-full border border-gray-200 focus:outline-none">
                    </button>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Açıklama</label>
                <textarea v-model="form.description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"></textarea>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="saveCategory" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
              Kaydet
            </button>
            <button @click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              İptal
            </button>
          </div>
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
      categories: [],
      showModal: false,
      isEdit: false,
      form: {
        id: null,
        name: '',
        color: '#EF4444',
        description: ''
      },
      colors: [
        '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#6B7280'
      ]
    }
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await axios.get('/api/finance/expense-categories');
        this.categories = response.data;
      } catch (error) {
        console.error('Error fetching categories:', error);
      }
    },
    openModal() {
      this.isEdit = false;
      this.form = { id: null, name: '', color: '#EF4444', description: '' };
      this.showModal = true;
    },
    editCategory(category) {
      this.isEdit = true;
      this.form = { ...category };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async saveCategory() {
      try {
        if (this.isEdit) {
            await axios.put(`/api/finance/expense-categories/${this.form.id}`, this.form);
        } else {
            await axios.post('/api/finance/expense-categories', this.form);
        }
        await this.fetchCategories();
        this.closeModal();
      } catch (error) {
        console.error('Error saving category:', error);
        alert('Kategori kaydedilemedi.');
      }
    },
    async deleteCategory(id) {
        if (!confirm('Bu kategoriyi silmek istediğinize emin misiniz?')) return;
        try {
            await axios.delete(`/api/finance/expense-categories/${id}`);
            this.fetchCategories();
        } catch (error) {
            console.error('Error deleting category:', error);
        }
    }
  }
}
</script>
