<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigTable from '@/components/ConfigTable.vue';

defineProps<{ accoes: { id: number; nome: string; ativo: boolean }[] }>();
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Configurações', href: '/configuracoes' },
            {
                title: 'Ações Calendário',
                href: '/configuracoes/acoes-calendario',
            },
        ]"
    >
        <div class="p-6">
            <ConfigTable
                title="Ações de Calendário"
                :items="accoes"
                :columns="[
                    { key: 'nome', label: 'Nome' },
                    { key: 'ativo', label: 'Estado' },
                ]"
                :fields="[
                    {
                        key: 'nome',
                        label: 'Nome',
                        placeholder: 'ex: Follow-up',
                    },
                    { key: 'ativo', label: 'Ativo', type: 'toggle' },
                ]"
                store-route="/configuracoes/acoes-calendario"
                :update-route="(id) => `/configuracoes/acoes-calendario/${id}`"
                :destroy-route="(id) => `/configuracoes/acoes-calendario/${id}`"
            />
        </div>
    </AppLayout>
</template>
