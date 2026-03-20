<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { fmtCurrency } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

type Fatura = {
    id: number;
    numero: string;
    data_fatura: string | null;
    entidade_nome: string | null;
    encomenda_numero: string | null;
    valor_total: number;
    caminho_documento: string | null;
    estado: 'pendente' | 'paga';
};

defineProps<{ faturas: Fatura[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    { title: 'Faturas Fornecedores', href: '/financeiro/faturas-fornecedores' },
];

const estadoLabel: Record<string, string> = {
    pendente: 'Pendente',
    paga: 'Paga',
};

const estadoClass: Record<string, string> = {
    pendente: 'text-yellow-600 font-medium',
    paga: 'text-green-600 font-medium',
};

const columns: ColumnDef<Fatura>[] = [
    {
        accessorKey: 'data_fatura',
        header: 'Data',
        cell: ({ row }) => row.original.data_fatura ?? '—',
    },
    { accessorKey: 'numero', header: 'Número' },
    {
        accessorKey: 'entidade_nome',
        header: 'Fornecedor',
        cell: ({ row }) => row.original.entidade_nome ?? '—',
    },
    {
        accessorKey: 'encomenda_numero',
        header: 'Encomenda',
        cell: ({ row }) => row.original.encomenda_numero ?? '—',
    },
    {
        accessorKey: 'caminho_documento',
        header: 'Documento',
        cell: ({ row }) => (row.original.caminho_documento ? '✓' : '—'),
    },
    {
        accessorKey: 'valor_total',
        header: 'Valor Total',
        cell: ({ row }) => fmtCurrency(row.original.valor_total),
    },
    {
        accessorKey: 'estado',
        header: 'Estado',
        cell: ({ row }) =>
            estadoLabel[row.original.estado] ?? row.original.estado,
    },
];

function destroy(f: Fatura): void {
    if (
        confirm(
            `Eliminar fatura "${f.numero}"? Esta ação não pode ser desfeita.`,
        )
    ) {
        router.delete(`/financeiro/faturas-fornecedores/${f.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Faturas — Fornecedores</h1>
                <Button as-child>
                    <Link href="/financeiro/faturas-fornecedores/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Nova
                    </Link>
                </Button>
            </div>

            <DataTable :data="faturas" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            as-child
                            title="Editar"
                        >
                            <Link
                                :href="`/financeiro/faturas-fornecedores/${row.id}/edit`"
                            >
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            title="Eliminar"
                            @click="destroy(row)"
                        >
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
