<script setup>
import ErrorView from '@/Pages/Errors/Error.vue';

const props = defineProps({
    status: {
        type: [Number, String],
        default: 404,
    },
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <ErrorView :status="props.status" :message="props.message" />
</template>
