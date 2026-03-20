<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Download, FileOutput, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Proposta = {
    id: number;
    numero: string;
    data_proposta: string | null;
    validade: string | null;
    entidade_nome: string | null;
    valor_total: number;
    estado: 'apresentada' | 'concluida' | 'rejeitada';
};

defineProps<{ propostas: Proposta[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Propostas', href: '/propostas' },
];

const estadoVariant: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    apresentada: 'secondary',
    concluida: 'default',
    rejeitada: 'destructive',
};

const estadoLabel: Record<string, string> = {
    apresentada: 'Apresentada',
    concluida: 'Concluída',
    rejeitada: 'Rejeitada',
};

const columns: ColumnDef<Proposta>[] = [
    {
        accessorKey: 'data_proposta',
        header: 'Data',
        cell: ({ row }) => row.original.data_proposta ?? '—',
    },
    { accessorKey: 'numero', header: 'Número' },
    {
        accessorKey: 'validade',
        header: 'Validade',
        cell: ({ row }) => row.original.validade ?? '—',
    },
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
    },
];

function destroy(p: Proposta): void {
    if (
        confirm(
            `Eliminar proposta "${p.numero}"? Esta ação não pode ser desfeita.`,
        )
    ) {
        router.delete(`/propostas/${p.id}`, { preserveScroll: true });
    }
}

function converter(p: Proposta): void {
    if (confirm(`Converter proposta "${p.numero}" em Encomenda de Cliente?`)) {
        router.post(`/propostas/${p.id}/converter`);
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Propostas</h1>
                <Button as-child>
                    <Link href="/propostas/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Nova
                    </Link>
                </Button>
            </div>

            <DataTable :data="propostas" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <!-- PDF -->
                        <Button
                            variant="ghost"
                            size="icon"
                            as-child
                            title="Exportar PDF"
                        >
                            <a
                                :href="`/propostas/${row.id}/pdf`"
                                target="_blank"
                            >
                                <Download class="h-4 w-4" />
                            </a>
                        </Button>
                        <!-- Converter para Encomenda -->
                        <Button
                            v-if="row.estado === 'concluida'"
                            variant="ghost"
                            size="icon"
                            title="Converter em Encomenda"
                            @click="converter(row)"
                        >
                            <FileOutput class="h-4 w-4 text-blue-600" />
                        </Button>
                        <!-- Editar -->
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="`/propostas/${row.id}/edit`">
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <!-- Eliminar -->
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
