<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigTable from '@/components/ConfigTable.vue';

defineProps<{
    taxas: { id: number; nome: string; taxa: number; ativo: boolean }[];
}>();
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Configurações', href: '/configuracoes' },
            { title: 'Taxas IVA', href: '/configuracoes/taxas-iva' },
        ]"
    >
        <div class="p-6">
            <ConfigTable
                title="Taxas de IVA"
                :items="taxas"
                :columns="[
                    { key: 'nome', label: 'Nome' },
                    { key: 'taxa', label: 'Taxa (%)' },
                    { key: 'ativo', label: 'Estado' },
                ]"
                :fields="[
                    {
                        key: 'nome',
                        label: 'Nome',
                        placeholder: 'ex: IVA Normal',
                    },
                    {
                        key: 'taxa',
                        label: 'Taxa (%)',
                        type: 'number',
                        placeholder: '23',
                    },
                    { key: 'ativo', label: 'Ativo', type: 'toggle' },
                ]"
                store-route="/configuracoes/taxas-iva"
                :update-route="(id) => `/configuracoes/taxas-iva/${id}`"
                :destroy-route="(id) => `/configuracoes/taxas-iva/${id}`"
            />
        </div>
    </AppLayout>
</template>
