<template>
    <div>
        <ErrorAlert :error="error" @dismiss="error = null" />
        <LoadingSpinner :show="loading" />
        
        <div class="md:grid md:grid-cols-3 md:gap-6" v-if="!loading || isEdit">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Müşteri Bilgileri</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Ticari ilişki kurduğunuz müşterilerinizi kaydedin.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form @submit.prevent="saveCustomer">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Müşteri / Firma Adı</label>
                                    <input type="text" name="name" id="name" v-model="form.name" required
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="email" v-model="form.email"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                                    <input type="text" name="phone" id="phone" v-model="form.phone"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6">
                                    <label for="address" class="block text-sm font-medium text-gray-700">Adres</label>
                                    <textarea id="address" name="address" rows="3" v-model="form.address"
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="tax_office" class="block text-sm font-medium text-gray-700">Vergi Dairesi</label>
                                    <input type="text" name="tax_office" id="tax_office" v-model="form.tax_office"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="tax_number" class="block text-sm font-medium text-gray-700">Vergi / Kimlik No</label>
                                    <input type="text" name="tax_number" id="tax_number" v-model="form.tax_number"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <button type="button" @click="$router.push('/customers')" class="mr-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                İptal
                            </button>
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
import apiClient from '../../api/client.js';
import toast from '../../utils/toast.js';
import ErrorAlert from '../../Components/ErrorAlert.vue';
import LoadingSpinner from '../../Components/LoadingSpinner.vue';

export default {
    components: {
        ErrorAlert,
        LoadingSpinner
    },
    data() {
        return {
            form: {
                id: null,
                name: '',
                email: '',
                phone: '',
                address: '',
                tax_office: '',
                tax_number: ''
            },
            isEdit: false,
            loading: false,
            error: null
        }
    },
    mounted() {
        const route = this.$route;
        if (route.params.id) {
            this.isEdit = true;
            this.fetchCustomer(route.params.id);
        }
    },
    methods: {
        async fetchCustomer(id) {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.get(`/customers/customers/${id}`);
                if (response.data.success) {
                    this.form = response.data.data;
                }
            } catch (error) {
                console.error(error);
                this.error = error.response?.data?.message || 'Müşteri bilgileri yüklenemedi.';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        },
        async saveCustomer() {
            this.loading = true;
            this.error = null;
            try {
                let response;
                
                if (this.isEdit) {
                    response = await apiClient.put(`/customers/customers/${this.form.id}`, this.form);
                    toast.success('Müşteri başarıyla güncellendi.');
                } else {
                    response = await apiClient.post('/customers/customers', this.form);
                    toast.success('Müşteri başarıyla eklendi.');
                }

                if (response.data.success) {
                    this.$router.push('/customers');
                }
            } catch (error) {
                console.error(error);
                this.error = error.response?.data?.message || 'Kaydetme hatası.';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
