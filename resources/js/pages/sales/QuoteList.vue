<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Teklifler
            </h2>
            <router-link to="/quotes/create" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Yeni Teklif Oluştur
            </router-link>
        </div>

        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teklif No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Geçerlilik</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">İşlem</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="quote in quotes" :key="quote.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ quote.quote_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ quote.customer ? quote.customer.name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                        {{ quote.total_amount }} ₺
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                            :class="{
                                                'bg-gray-100 text-gray-800': quote.status === 'draft',
                                                'bg-green-100 text-green-800': quote.status === 'converted',
                                                'bg-blue-100 text-blue-800': quote.status === 'sent'
                                            }">
                                            {{ translateStatus(quote.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(quote.valid_until) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button v-if="quote.status !== 'converted'" @click="convertToOrder(quote)" class="text-indigo-600 hover:text-indigo-900 mr-4">Siparişe Çevir</button>
                                        <router-link :to="`/quotes/${quote.id}`" class="text-gray-600 hover:text-gray-900">Detay</router-link>
                                    </td>
                                </tr>
                                <tr v-if="quotes.length === 0">
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        Henüz teklif bulunmuyor.
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
import axios from 'axios';

export default {
    data() {
        return {
            quotes: []
        }
    },
    mounted() {
        this.fetchQuotes();
    },
    methods: {
        async fetchQuotes() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/sales/quotes', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.quotes = response.data.data.data; // Paginated response usually .data.data
                }
            } catch (error) {
                console.error('Error fetching quotes:', error);
            }
        },
        async convertToOrder(quote) {
            if (!confirm('Bu teklifi siparişe dönüştürmek istediğinize emin misiniz? Stoktan düşülecek.')) return;

            try {
                const token = localStorage.getItem('token');
                const response = await axios.post(`/api/sales/quotes/${quote.id}/convert`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                
                if (response.data.success) {
                    alert('Sipariş başarıyla oluşturuldu!');
                    this.fetchQuotes();
                }
            } catch (error) {
                console.error(error);
                alert('Hata: ' + (error.response?.data?.message || error.message));
            }
        },
        translateStatus(status) {
            const map = {
                'draft': 'Taslak',
                'sent': 'Gönderildi',
                'accepted': 'Kabul Edildi',
                'rejected': 'Reddedildi',
                'converted': 'Sip. Dönüştü'
            };
            return map[status] || status;
        },
        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('tr-TR');
        }
    }
}
</script>
