<template>
    <div v-if="show" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="close"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ type === 'collection' ? 'Tahsilat Ekle (Gelir)' : 'Ödeme Yap (Gider)' }}
                            </h3>
                            <div class="mt-2 text-sm text-gray-500 mb-4">
                                {{ entityName }} için işlem yapılıyor.
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tutar</label>
                                    <input type="number" step="0.01" v-model="form.amount" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tarih</label>
                                    <input type="date" v-model="form.transaction_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ödeme Yöntemi</label>
                                    <select v-model="form.method" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm sm:text-sm">
                                        <option value="cash">Nakit</option>
                                        <option value="credit_card">Kredi Kartı</option>
                                        <option value="bank_transfer">Havale/EFT</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Açıklama</label>
                                    <textarea v-model="form.description" rows="2" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="save" :disabled="!form.amount" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        Kaydet
                    </button>
                    <button type="button" @click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        İptal
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        show: Boolean,
        type: String, // 'collection' or 'payment'
        payableType: String, // 'customer' or 'supplier'
        payableId: Number,
        entityName: String
    },
    data() {
        return {
            form: {
                amount: null,
                transaction_date: new Date().toISOString().split('T')[0],
                method: 'cash',
                description: ''
            }
        };
    },
    methods: {
        close() {
            this.$emit('close');
            this.resetForm();
        },
        resetForm() {
            this.form = {
                amount: null,
                transaction_date: new Date().toISOString().split('T')[0],
                method: 'cash',
                description: ''
            };
        },
        async save() {
            try {
                const token = localStorage.getItem('token');
                await axios.post('/api/finance/transactions', {
                    ...this.form,
                    type: this.type,
                    payable_type: this.payableType,
                    payable_id: this.payableId
                }, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                
                this.$emit('saved');
                this.close();
            } catch (error) {
                console.error(error);
                alert('İşlem kaydedilemedi.');
            }
        }
    }
}
</script>
