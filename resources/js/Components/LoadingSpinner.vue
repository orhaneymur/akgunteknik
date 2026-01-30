<template>
    <div v-if="show" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>
</template>

<script>
export default {
    name: 'LoadingSpinner',
    props: {
        show: {
            type: Boolean,
            default: true,
        },
    },
};
</script>
