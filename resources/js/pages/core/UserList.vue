<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Personel Yönetimi
            </h2>
            <div class="flex">
                <router-link to="/users/create" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Yeni Personel Ekle
                </router-link>
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
                                    İsim
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rol
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Şube
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ user.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ user.email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span :class="getRoleBadgeClass(user.role)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ getRoleLabel(user.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ user.branch ? user.branch.name : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <!-- Add edit functionality later if needed, for now just delete -->
                                    <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900 ml-4">Sil</button>
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
            users: []
        };
    },
    mounted() {
        this.fetchUsers();
    },
    methods: {
        async fetchUsers() {
            try {
                const token = localStorage.getItem('token');
                const response = await axios.get('/api/core/users', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.users = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching users:', error);
            }
        },
        async deleteUser(id) {
            if (!confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')) return;
            
            try {
                const token = localStorage.getItem('token');
                const response = await axios.delete(`/api/core/users/${id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (response.data.success) {
                    this.fetchUsers();
                }
            } catch (error) {
                console.error(error);
                alert('Silme işlemi başarısız. Kendinizi silemezsiniz.');
            }
        },
        getRoleLabel(role) {
            const labels = {
                'owner': 'Sahip',
                'manager': 'Yönetici',
                'staff': 'Personel'
            };
            return labels[role] || role;
        },
        getRoleBadgeClass(role) {
            const classes = {
                'owner': 'bg-purple-100 text-purple-800',
                'manager': 'bg-blue-100 text-blue-800',
                'staff': 'bg-green-100 text-green-800'
            };
            return classes[role] || 'bg-gray-100 text-gray-800';
        }
    }
}
</script>
