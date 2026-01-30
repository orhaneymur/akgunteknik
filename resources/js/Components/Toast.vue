<template>
    <transition-group name="toast" tag="div" class="fixed top-4 right-4 z-50 space-y-2">
        <div
            v-for="toast in toasts"
            :key="toast.id"
            :class="[
                'max-w-sm w-full shadow-lg rounded-lg pointer-events-auto',
                toast.type === 'success' ? 'bg-green-50 border border-green-200' : '',
                toast.type === 'error' ? 'bg-red-50 border border-red-200' : '',
                toast.type === 'info' ? 'bg-blue-50 border border-blue-200' : '',
                toast.type === 'warning' ? 'bg-yellow-50 border border-yellow-200' : ''
            ]"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg v-if="toast.type === 'success'" class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="toast.type === 'error'" class="h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p :class="[
                            'text-sm font-medium',
                            toast.type === 'success' ? 'text-green-800' : '',
                            toast.type === 'error' ? 'text-red-800' : '',
                            toast.type === 'info' ? 'text-blue-800' : '',
                            toast.type === 'warning' ? 'text-yellow-800' : ''
                        ]">
                            {{ toast.message }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button
                            @click="removeToast(toast.id)"
                            :class="[
                                'inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
                                toast.type === 'success' ? 'text-green-500 hover:text-green-600 focus:ring-green-500' : '',
                                toast.type === 'error' ? 'text-red-500 hover:text-red-600 focus:ring-red-500' : '',
                                toast.type === 'info' ? 'text-blue-500 hover:text-blue-600 focus:ring-blue-500' : '',
                                toast.type === 'warning' ? 'text-yellow-500 hover:text-yellow-600 focus:ring-yellow-500' : ''
                            ]"
                        >
                            <span class="sr-only">Kapat</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition-group>
</template>

<script>
export default {
    name: 'Toast',
    data() {
        return {
            toasts: [],
            toastId: 0
        }
    },
    mounted() {
        // Listen to custom DOM events (Vue 3 compatible)
        window.addEventListener('toast', (event) => {
            this.showToast(event.detail);
        });
    },
    beforeUnmount() {
        window.removeEventListener('toast', this.showToast);
    },
    methods: {
        showToast({ message, type = 'info', duration = 5000 }) {
            const id = ++this.toastId;
            const toast = { id, message, type };
            this.toasts.push(toast);

            if (duration > 0) {
                setTimeout(() => {
                    this.removeToast(id);
                }, duration);
            }
        },
        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) {
                this.toasts.splice(index, 1);
            }
        }
    }
}
</script>

<style scoped>
.toast-enter-active, .toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
