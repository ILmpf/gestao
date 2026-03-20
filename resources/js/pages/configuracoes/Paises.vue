<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigTable from '@/components/ConfigTable.vue';

defineProps<{
    paises: { id: number; nome: string; codigo: string; ativo: boolean }[];
}>();
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Configurações', href: '/configuracoes' },
            { title: 'Países', href: '/configuracoes/paises' },
        ]"
    >
        <div class="p-6">
            <ConfigTable
                title="Países"
                :items="paises"
                :columns="[
                    { key: 'nome', label: 'Nome' },
                    { key: 'codigo', label: 'Código' },
                    { key: 'ativo', label: 'Estado' },
                ]"
                :fields="[
                    { key: 'nome', label: 'Nome', placeholder: 'ex: Portugal' },
                    {
                        key: 'codigo',
                        label: 'Código ISO',
                        placeholder: 'ex: PT',
                    },
                    { key: 'ativo', label: 'Ativo', type: 'toggle' },
                ]"
                store-route="/configuracoes/paises"
                :update-route="(id) => `/configuracoes/paises/${id}`"
                :destroy-route="(id) => `/configuracoes/paises/${id}`"
            />
        </div>
    </AppLayout>
</template>
