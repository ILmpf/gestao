<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Download, Eye, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Encomenda = {
    id: number;
    numero: string;
    data_encomenda: string | null;
    entidade_nome: string | null;
    valor_total: number;
    estado: 'em_progresso' | 'concluida' | 'cancelada';
};

defineProps<{ encomendas: Encomenda[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Encomendas', href: '/encomendas' },
    { title: 'Fornecedores', href: '/encomendas/fornecedores' },
];

const estadoLabel: Record<string, string> = {
    em_progresso: 'Em Progresso',
    concluida: 'Concluída',
    cancelada: 'Cancelada',
};

const columns: ColumnDef<Encomenda>[] = [
    {
        accessorKey: 'data_encomenda',
        header: 'Data',
        cell: ({ row }) => row.original.data_encomenda ?? '—',
    },
    { accessorKey: 'numero', header: 'Número' },
    {
        accessorKey: 'entidade_nome',
        header: 'Fornecedor',
        cell: ({ row }) => row.original.entidade_nome ?? '—',
    },
    {
        accessorKey: 'valor_total',
        header: 'Valor Total',
        cell: ({ row }) =>
            new Intl.NumberFormat('pt-PT', {
                style: 'currency',
                currency: 'EUR',
            }).format(row.original.valor_total),
    },
    {
        accessorKey: 'estado',
        header: 'Estado',
        cell: ({ row }) =>
            estadoLabel[row.original.estado] ?? row.original.estado,
    },
];

function destroy(e: Encomenda): void {
    if (
        confirm(
            `Eliminar encomenda "${e.numero}"? Esta ação não pode ser desfeita.`,
        )
    ) {
        router.delete(`/encomendas/fornecedores/${e.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Encomendas — Fornecedores</h1>
            </div>

            <DataTable :data="encomendas" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            as-child
                            title="PDF"
                        >
                            <a
                                :href="`/encomendas/fornecedores/${row.id}/pdf`"
                                target="_blank"
                            >
                                <Download class="h-4 w-4" />
                            </a>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            as-child
                            title="Ver detalhe"
                        >
                            <Link :href="`/encomendas/fornecedores/${row.id}`">
                                <Eye class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="text-destructive hover:text-destructive"
                            @click="destroy(row)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
