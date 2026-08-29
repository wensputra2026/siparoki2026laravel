<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CrudTable from '@/Components/CrudTable.vue';

defineProps({
    type: String,
    label: String,
    section: String,
    parentFk: { type: String, default: null },
    grup: { type: [String, Number], default: null },
    fields: { type: Array, default: () => [] },
    tableColumns: { type: Array, default: () => [] },
    fkOptions: { type: Object, default: () => ({}) },
    rows: { type: Object, default: () => ({ data: [] }) },
});
</script>

<template>
    <AppLayout :title="label" :fullWidth="true">
        <Head :title="`${label} - Master Referensi`" />
        <CrudTable
            :type="type"
            :label="label"
            :parent-fk="parentFk"
            :grup="grup"
            :fields="fields"
            :table-columns="tableColumns"
            :fk-options="fkOptions"
            :rows="rows"
        />
    </AppLayout>
</template>
