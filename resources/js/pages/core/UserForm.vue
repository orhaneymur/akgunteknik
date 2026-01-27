<template>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Personel Bilgileri</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Yeni bir personel hesabı oluşturun veya mevcut hesabın bilgilerini güncelleyin.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form @submit.prevent="submitForm">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="name" class="block text-sm font-medium text-gray-700">İsim Soyisim</label>
                                    <input type="text" id="name" v-model="form.name" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email Adresi</label>
                                    <input type="email" id="email" v-model="form.email" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
                                    <input type="password" id="password" v-model="form.password" :required="!isEdit" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <p v-if="isEdit" class="mt-1 text-xs text-gray-500">Boş bırakırsanız değişmez.</p>
                                </div>
                                
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="role" class="block text-sm font-medium text-gray-700">Rol (Yetki)</label>
                                    <select id="role" v-model="form.role" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="staff">Personel</option>
                                        <option value="manager">Yönetici</option>
                                        <option value="owner">Sahip</option>
                                    </select>
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="warehouse" class="block text-sm font-medium text-gray-700">Atanan Şube</label>
                                    <select id="warehouse" v-model="form.warehouse_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Merkez (Şube Yok)</option>
                                        <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                            {{ warehouse.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                             <router-link to="/users" class="mr-4 text-sm text-gray-700 hover:text-gray-500">İptal</router-link>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Kaydet
                            </button>
                        </div>
                    </div>
                </form>
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
                name: '',
                email: '',
                password: '',
                role: 'staff',
                warehouse_id: '',
            },
            warehouses: [],
            isEdit: false
        }
    },
    mounted() {
        this.fetchWarehouses();
        // Check if editing (will implement user edit later if needed, for now mainly create)
    },
    methods: {
        async fetchWarehouses() {
             try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/core/warehouses', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.warehouses = response.data.data;
                }
            } catch (err) {
                console.error(err);
            }
        },
        async submitForm() {
            try {
                const token = localStorage.getItem('token');
                await axios.post('/api/core/users', this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                this.$router.push('/users');
            } catch (error) {
                console.error(error);
                alert('Kaydetme hatası: ' + (error.response?.data?.message || 'Bilinmeyen hata'));
            }
        }
    }
}
</script>
