<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { fmtCurrency } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

type Nota = {
    id: number;
    numero: string;
    data_nota_credito: string | null;
    entidade_nome: string | null;
    fatura_numero: string | null;
    valor_total: number;
    estado: 'pendente' | 'processada';
};

defineProps<{ notas: Nota[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    {
        title: 'Notas de Crédito Fornecedores',
        href: '/financeiro/notas-credito-fornecedores',
    },
];

const estadoLabel: Record<string, string> = {
    pendente: 'Pendente',
    processada: 'Processada',
};

const estadoClass: Record<string, string> = {
    pendente: 'text-yellow-600 font-medium',
    processada: 'text-green-600 font-medium',
};

const columns: ColumnDef<Nota>[] = [
    {
        accessorKey: 'data_nota_credito',
        header: 'Data',
        cell: ({ row }) => row.original.data_nota_credito ?? '—',
    },
    { accessorKey: 'numero', header: 'Número' },
    {
        accessorKey: 'entidade_nome',
        header: 'Fornecedor',
        cell: ({ row }) => row.original.entidade_nome ?? '—',
    },
    {
        accessorKey: 'fatura_numero',
        header: 'Fatura',
        cell: ({ row }) => row.original.fatura_numero ?? '—',
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

function destroy(n: Nota): void {
    if (
        confirm(
            `Eliminar nota de crédito "${n.numero}"? Esta ação não pode ser desfeita.`,
        )
    ) {
        router.delete(`/financeiro/notas-credito-fornecedores/${n.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">
                    Notas de Crédito — Fornecedores
                </h1>
                <Button as-child>
                    <Link href="/financeiro/notas-credito-fornecedores/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Nova
                    </Link>
                </Button>
            </div>

            <DataTable :data="notas" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center gap-2">
                        <span :class="estadoClass[row.estado]">{{
                            estadoLabel[row.estado]
                        }}</span>
                        <Button variant="ghost" size="icon" as-child>
                            <Link
                                :href="`/financeiro/notas-credito-fornecedores/${row.id}/edit`"
                            >
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="destroy(row)"
                        >
                            <Trash2 class="h-4 w-4 text-red-500" />
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
