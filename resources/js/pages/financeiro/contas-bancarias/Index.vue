<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { fmtCurrency } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

type Conta = {
    id: number;
    nome: string;
    iban: string | null;
    bic: string | null;
    ativa: boolean;
};

defineProps<{ contas: Conta[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    { title: 'Contas Bancárias', href: '/financeiro/contas-bancarias' },
];

const columns: ColumnDef<Conta>[] = [
    { accessorKey: 'nome', header: 'Nome' },
    {
        accessorKey: 'iban',
        header: 'IBAN',
        cell: ({ row }) => row.original.iban ?? '—',
    },
    {
        accessorKey: 'bic',
        header: 'BIC',
        cell: ({ row }) => row.original.bic ?? '—',
    },
    {
        accessorKey: 'saldo',
        header: 'Saldo',
        cell: ({ row }) => fmtCurrency(row.original.saldo),
    },
    {
        accessorKey: 'ativa',
        header: 'Estado',
        cell: ({ row }) => (row.original.ativa ? 'Ativa' : 'Inativa'),
    },
];

function destroy(c: Conta): void {
    if (
        confirm(`Eliminar conta "${c.nome}"? Esta ação não pode ser desfeita.`)
    ) {
        router.delete(`/financeiro/contas-bancarias/${c.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Contas Bancárias</h1>
                <Button as-child>
                    <Link href="/financeiro/contas-bancarias/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Nova
                    </Link>
                </Button>
            </div>

            <DataTable :data="contas" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            as-child
                            title="Editar"
                        >
                            <Link
                                :href="`/financeiro/contas-bancarias/${row.id}/edit`"
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
