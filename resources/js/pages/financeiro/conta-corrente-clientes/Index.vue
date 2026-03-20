<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import DataTable from '@/components/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { fmtCurrency } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

type ClienteTotais = {
    entidade_id: number;
    entidade_nome: string | null;
    total_debito: number;
    total_credito: number;
    saldo_atual: number;
};

defineProps<{ clientes: ClienteTotais[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    {
        title: 'Conta Corrente Clientes',
        href: '/financeiro/conta-corrente-clientes',
    },
];

const fmt = fmtCurrency;

const columns: ColumnDef<ClienteTotais>[] = [
    {
        accessorKey: 'entidade_nome',
        header: 'Cliente',
        cell: ({ row }) => row.original.entidade_nome ?? '—',
    },
    {
        accessorKey: 'total_debito',
        header: 'Débito Total',
        cell: ({ row }) => fmt(row.original.total_debito),
    },
    {
        accessorKey: 'total_credito',
        header: 'Crédito Total',
        cell: ({ row }) => fmt(row.original.total_credito),
    },
    {
        accessorKey: 'saldo_atual',
        header: 'Saldo Atual',
        cell: ({ row }) => fmt(row.original.saldo_atual),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Conta Corrente — Clientes</h1>
            </div>

            <DataTable :data="clientes" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end">
                        <button
                            class="text-sm text-primary underline-offset-4 hover:underline"
                            @click="
                                router.visit(
                                    `/financeiro/conta-corrente-clientes/${row.entidade_id}`,
                                )
                            "
                        >
                            Ver detalhe
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
