<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigTable from '@/components/ConfigTable.vue';

defineProps<{
    tipos: { id: number; nome: string; cor: string; ativo: boolean }[];
}>();
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Configurações', href: '/configuracoes' },
            {
                title: 'Tipos Calendário',
                href: '/configuracoes/tipos-calendario',
            },
        ]"
    >
        <div class="p-6">
            <ConfigTable
                title="Tipos de Calendário"
                :items="tipos"
                :columns="[
                    { key: 'nome', label: 'Nome' },
                    { key: 'cor', label: 'Cor' },
                    { key: 'ativo', label: 'Estado' },
                ]"
                :fields="[
                    { key: 'nome', label: 'Nome', placeholder: 'ex: Reunião' },
                    { key: 'cor', label: 'Cor', type: 'color' },
                    { key: 'ativo', label: 'Ativo', type: 'toggle' },
                ]"
                store-route="/configuracoes/tipos-calendario"
                :update-route="(id) => `/configuracoes/tipos-calendario/${id}`"
                :destroy-route="(id) => `/configuracoes/tipos-calendario/${id}`"
            />
        </div>
    </AppLayout>
</template>
