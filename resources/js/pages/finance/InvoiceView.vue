<template>
    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8 print:p-0 print:max-w-none">
        
        <!-- Controls (Hidden on Print) -->
        <div class="mb-8 flex justify-between print:hidden">
            <router-link to="/invoices" class="text-indigo-600 hover:text-indigo-900">← Listeye Dön</router-link>
            <button @click="printInvoice" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Yazdır / PDF Kaydet
            </button>
        </div>

        <!-- A4 Page Container -->
        <div class="bg-white shadow-lg p-10 print:shadow-none print:p-0" id="invoice">
            
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-gray-200 pb-8 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">FATURA</h1>
                    <p class="text-sm text-gray-500 mt-1">#{{ invoice.invoice_number }}</p>
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold text-gray-900">AKGÜN TEKNİK</h2>
                    <p class="text-sm text-gray-500">Orhan Eymür</p>
                    <p class="text-sm text-gray-500">info@akgunteknik.com</p>
                </div>
            </div>

            <!-- Details -->
            <div class="flex justify-between mb-8">
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sayın</h3>
                    <p class="text-lg font-medium text-gray-900">{{ invoice.contact_name }}</p>
                </div>
                <div class="text-right">
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Düzenleme Tarihi:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ formatDate(invoice.issue_date) }}</span>
                    </div>
                    <div class="mt-2">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Son Ödeme Tarihi:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ formatDate(invoice.due_date) }}</span>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="mb-8">
                <table class="min-w-full divide-y divide-gray-200 border-t border-b border-gray-200">
                    <thead>
                        <tr>
                            <th scope="col" class="py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama / Ürün</th>
                            <th scope="col" class="py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Miktar</th>
                            <th scope="col" class="py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Birim Fiyat</th>
                            <th scope="col" class="py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="item in invoice.items" :key="item.id">
                            <td class="py-4 text-sm text-gray-900">{{ item.description }}</td>
                            <td class="py-4 text-right text-sm text-gray-500">{{ parseFloat(item.quantity) }}</td>
                            <td class="py-4 text-right text-sm text-gray-500">{{ formatCurrency(item.unit_price) }}</td>
                            <td class="py-4 text-right text-sm text-gray-900 font-medium">{{ formatCurrency(item.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="flex justify-end">
                <div class="w-64">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-sm font-medium text-gray-500">Ara Toplam</span>
                        <span class="text-sm font-medium text-gray-900">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-sm font-medium text-gray-500">KDV (0%)</span> <!-- Simplified for now -->
                        <span class="text-sm font-medium text-gray-900">{{ formatCurrency(0) }}</span>
                    </div>
                    <div class="flex justify-between py-4">
                        <span class="text-lg font-bold text-gray-900">Genel Toplam</span>
                        <span class="text-lg font-bold text-indigo-600">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
             <div class="mt-16 pt-8 border-t border-gray-200 text-center text-xs text-gray-400">
                <p>Teşekkür ederiz.</p>
                <p>Bu belge elektronik ortamda oluşturulmuştur.</p>
            </div>

        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            invoice: {
                items: []
            }
        };
    },
    mounted() {
        this.fetchInvoice();
    },
    methods: {
        async fetchInvoice() {
            try {
                const token = localStorage.getItem('token');
                const id = this.$route.params.id;
                const response = await axios.get(`/api/finance/invoices/${id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.invoice = response.data.data;
                }
            } catch (error) {
                console.error(error);
                alert('Fatura bulunamadı.');
                this.$router.push('/invoices');
            }
        },
        printInvoice() {
            window.print();
        },
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('tr-TR');
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
        }
    }
}
</script>

<style scoped>
@media print {
    /* Hide everything that is NOT the invoice content when printing? 
       Actually with the 'print:hidden' classes above, we are mostly good. 
       Tailwind's 'print' modifier handles this. */
}
</style>
