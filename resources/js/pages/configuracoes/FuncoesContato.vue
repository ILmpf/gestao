<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigTable from '@/components/ConfigTable.vue';

defineProps<{ funcoes: { id: number; nome: string; ativo: boolean }[] }>();
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Configurações', href: '/configuracoes' },
            {
                title: 'Funções Contacto',
                href: '/configuracoes/funcoes-contacto',
            },
        ]"
    >
        <div class="p-6">
            <ConfigTable
                title="Funções de Contacto"
                :items="funcoes"
                :columns="[
                    { key: 'nome', label: 'Nome' },
                    { key: 'ativo', label: 'Estado' },
                ]"
                :fields="[
                    {
                        key: 'nome',
                        label: 'Nome',
                        placeholder: 'ex: Diretor Comercial',
                    },
                    { key: 'ativo', label: 'Ativo', type: 'toggle' },
                ]"
                store-route="/configuracoes/funcoes-contacto"
                :update-route="(id) => `/configuracoes/funcoes-contacto/${id}`"
                :destroy-route="(id) => `/configuracoes/funcoes-contacto/${id}`"
            />
        </div>
    </AppLayout>
</template>
