<template>
    <div class="h-screen flex overflow-hidden bg-gradient-to-br from-gray-50 via-white to-gray-100">
        <!-- Sidebar -->
        <!-- Logic: On desktop (md+), visible always. On mobile, toggleable. -->
        <Sidebar :isOpen="sidebarOpen" :isMobile="isMobile" class="hidden md:flex" />

        <!-- Mobile Sidebar Overlay & Component -->
         <div v-if="sidebarOpen" class="fixed inset-0 flex z-40 md:hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="sidebarOpen = false"></div>
            <Sidebar :isOpen="true" :isMobile="true" class="relative flex-1 flex flex-col max-w-xs w-full" />
            <div class="flex-shrink-0 w-14" aria-hidden="true"></div>
        </div>


        <!-- Main Content Column -->
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <Topbar @toggle-sidebar="sidebarOpen = true" />

            <main class="flex-1 relative overflow-y-auto focus:outline-none bg-gradient-to-br from-gray-50 via-white to-gray-100">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        <router-view></router-view>
                    </div>
                </div>
            </main>
        </div>
        
        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>

<script>
import Sidebar from './Sidebar.vue';
import Topbar from './Topbar.vue';
import Toast from '../Components/Toast.vue';

export default {
    name: "AppLayout",
    components: {
        Sidebar,
        Topbar,
        Toast
    },
    data() {
        return {
            sidebarOpen: false,
            isMobile: false
        }
    },
    mounted() {
        this.checkScreen();
        window.addEventListener('resize', this.checkScreen);
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.checkScreen);
    },
    methods: {
        checkScreen() {
            this.isMobile = window.innerWidth < 768; // Tailwind md breakpoint
            if (!this.isMobile) {
                this.sidebarOpen = true; // Always open on desktop
            } else {
                this.sidebarOpen = false; // Closed by default on mobile
            }
        }
    }
}
</script>
