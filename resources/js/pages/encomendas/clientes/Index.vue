<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import {
    FileText,
    FileMinus,
    Download,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
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
    tem_fatura: boolean;
    tem_nota_credito: boolean;
};

defineProps<{ encomendas: Encomenda[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Encomendas', href: '/encomendas' },
    { title: 'Clientes', href: '/encomendas/clientes' },
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
        header: 'Cliente',
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
        router.delete(`/encomendas/clientes/${e.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Encomendas — Clientes</h1>
                <Button as-child>
                    <Link href="/encomendas/clientes/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Nova
                    </Link>
                </Button>
            </div>

            <DataTable :data="encomendas" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button
                            v-if="row.tem_fatura"
                            variant="ghost"
                            size="icon"
                            as-child
                            title="Descarregar Fatura"
                        >
                            <a
                                :href="`/encomendas/clientes/${row.id}/fatura`"
                                target="_blank"
                            >
                                <FileText class="h-4 w-4 text-blue-600" />
                            </a>
                        </Button>
                        <Button
                            v-if="row.tem_nota_credito"
                            variant="ghost"
                            size="icon"
                            as-child
                            title="Descarregar Nota de Crédito"
                        >
                            <a
                                :href="`/encomendas/clientes/${row.id}/nota-credito`"
                                target="_blank"
                            >
                                <FileMinus class="h-4 w-4 text-green-600" />
                            </a>
                        </Button>
                        <Button
                            v-if="!row.tem_fatura && !row.tem_nota_credito"
                            variant="ghost"
                            size="icon"
                            as-child
                            title="Descarregar Encomenda"
                        >
                            <a
                                :href="`/encomendas/clientes/${row.id}/pdf`"
                                target="_blank"
                            >
                                <Download class="h-4 w-4" />
                            </a>
                        </Button>
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="`/encomendas/clientes/${row.id}/edit`">
                                <Pencil class="h-4 w-4" />
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
