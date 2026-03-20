<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Entidade = {
    id: number;
    tipos: string[];
    numero_cliente: number | null;
    numero_fornecedor: number | null;
    nif: string | null;
    nome: string;
    telefone: string | null;
    telemovel: string | null;
    website: string | null;
    email: string | null;
    estado: 'ativo' | 'inativo';
};

const props = defineProps<{
    tipo: 'cliente' | 'fornecedor' | null;
    entidades: Entidade[];
}>();

const label =
    props.tipo === 'cliente'
        ? 'Clientes'
        : props.tipo === 'fornecedor'
          ? 'Fornecedores'
          : 'Entidades';
const baseHref =
    props.tipo === 'cliente'
        ? '/clientes'
        : props.tipo === 'fornecedor'
          ? '/fornecedores'
          : '/entidades';
const showCreate = props.tipo === null;

const breadcrumbs: BreadcrumbItem[] = [{ title: label, href: baseHref }];

const columns: ColumnDef<Entidade>[] = [
    {
        accessorKey: 'nif',
        header: 'NIF',
        cell: ({ row }) => row.original.nif ?? '—',
    },
    {
        accessorKey: 'nome',
        header: 'Nome',
    },
    {
        accessorKey: 'telefone',
        header: 'Telefone',
        cell: ({ row }) => row.original.telefone ?? '—',
    },
    {
        accessorKey: 'telemovel',
        header: 'Telemóvel',
        cell: ({ row }) => row.original.telemovel ?? '—',
    },
    {
        accessorKey: 'website',
        header: 'Website',
        cell: ({ row }) => row.original.website ?? '—',
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => row.original.email ?? '—',
    },
    {
        accessorKey: 'estado',
        header: 'Estado',
    },
];

function destroy(entidade: Entidade): void {
    if (
        confirm(`Eliminar "${entidade.nome}"? Esta ação não pode ser desfeita.`)
    ) {
        router.delete(`${baseHref}/${entidade.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">{{ label }}</h1>
                <Button v-if="showCreate" as-child>
                    <Link href="/entidades/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Nova Entidade
                    </Link>
                </Button>
            </div>

            <DataTable :data="entidades" :columns="columns">
                <template #actions="{ row }">
                    <div v-if="showCreate" class="flex items-center justify-end gap-1">
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="`/entidades/${row.id}/edit`">
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
