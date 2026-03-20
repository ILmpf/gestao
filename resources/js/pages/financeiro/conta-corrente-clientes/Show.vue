<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Download, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { fmtCurrency } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

type Movimento = {
    id: number;
    numero_documento: string | null;
    data_documento: string | null;
    data_vencimento: string | null;
    data: string | null;
    descricao: string;
    tipo: 'manual' | 'encomenda' | 'nota_credito';
    encomenda_cliente_id: number | null;
    debito: number;
    credito: number;
    saldo: number;
};

type Entidade = { id: number; nome: string };

const props = defineProps<{
    entidade: Entidade;
    movimentos: Movimento[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    {
        title: 'Conta Corrente Clientes',
        href: '/financeiro/conta-corrente-clientes',
    },
    { title: props.entidade.nome, href: '#' },
];

const fmt = fmtCurrency;

const tipoLabel: Record<string, string> = {
    manual: 'Pagamento',
    encomenda: 'Fatura',
    nota_credito: 'Nota de Crédito',
};

const tipoClass: Record<string, string> = {
    manual: 'bg-purple-100 text-purple-700',
    encomenda: 'bg-blue-100 text-blue-700',
    nota_credito: 'bg-green-100 text-green-700',
};

const columns: ColumnDef<Movimento>[] = [
    {
        accessorKey: 'numero_documento',
        header: 'Nº Documento',
        cell: ({ row }) => row.original.numero_documento ?? '—',
    },
    {
        accessorKey: 'data_documento',
        header: 'Data Documento',
        cell: ({ row }) => row.original.data_documento ?? '—',
    },
    {
        accessorKey: 'data_vencimento',
        header: 'Data Vencimento',
        cell: ({ row }) => row.original.data_vencimento ?? '—',
    },
    {
        accessorKey: 'data',
        header: 'Data Lançamento',
        cell: ({ row }) => row.original.data ?? '—',
    },
    {
        accessorKey: 'debito',
        header: 'Débito',
        cell: ({ row }) => fmt(row.original.debito),
    },
    {
        accessorKey: 'credito',
        header: 'Crédito',
        cell: ({ row }) => fmt(row.original.credito),
    },
    { accessorKey: 'descricao', header: 'Descrição' },
    {
        accessorKey: 'saldo',
        header: 'Saldo',
        cell: ({ row }) => fmt(row.original.saldo),
    },
];

function destroy(m: Movimento): void {
    if (confirm('Eliminar este movimento? Esta ação não pode ser desfeita.')) {
        router.delete(`/financeiro/conta-corrente-clientes/${m.id}`, {
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
                    {{ entidade.nome }}
                </h1>
                <Button as-child>
                    <Link
                        :href="`/financeiro/conta-corrente-clientes/${entidade.id}/create`"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Novo Movimento
                    </Link>
                </Button>
            </div>

            <DataTable :data="movimentos" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button
                            v-if="
                                row.tipo !== 'manual' &&
                                row.encomenda_cliente_id
                            "
                            variant="ghost"
                            size="icon"
                            as-child
                            :title="
                                row.tipo === 'encomenda'
                                    ? 'Descarregar Fatura'
                                    : 'Descarregar Nota de Crédito'
                            "
                        >
                            <a
                                :href="`/encomendas/clientes/${row.encomenda_cliente_id}/${row.tipo === 'encomenda' ? 'fatura' : 'nota-credito'}`"
                                target="_blank"
                            >
                                <Download class="h-4 w-4" />
                            </a>
                        </Button>
                        <span
                            v-if="row.tipo !== 'manual'"
                            :class="[
                                'inline-flex items-center rounded px-2 py-0.5 text-xs font-medium',
                                tipoClass[row.tipo],
                            ]"
                        >
                            {{ tipoLabel[row.tipo] }}
                        </span>
                        <template v-if="row.tipo === 'manual'">
                            <Button
                                variant="ghost"
                                size="icon"
                                as-child
                                title="Editar"
                            >
                                <Link
                                    :href="`/financeiro/conta-corrente-clientes/${row.id}/edit`"
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
                        </template>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
