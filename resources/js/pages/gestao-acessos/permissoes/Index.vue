<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Permissao = {
    id: number;
    name: string;
    users_count: number;
    permissions_count: number;
};

defineProps<{ permissoes: Permissao[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestão de Acessos', href: '/gestao-acessos/utilizadores' },
    { title: 'Permissões', href: '/gestao-acessos/permissoes' },
];

const columns: ColumnDef<Permissao>[] = [
    {
        accessorKey: 'name',
        header: 'Nome do Grupo',
    },
    {
        accessorKey: 'users_count',
        header: 'Utilizadores',
        cell: ({ row }) => row.original.users_count,
    },
    {
        accessorKey: 'permissions_count',
        header: 'Permissões Atribuídas',
        cell: ({ row }) => row.original.permissions_count,
    },
];

function destroy(permissao: Permissao): void {
    if (
        confirm(
            `Eliminar o grupo "${permissao.name}"? Esta ação não pode ser desfeita.`,
        )
    ) {
        router.delete(`/gestao-acessos/permissoes/${permissao.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Grupos de Permissões</h1>
                <Button as-child>
                    <Link href="/gestao-acessos/permissoes/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Novo Grupo
                    </Link>
                </Button>
            </div>

            <DataTable :data="permissoes" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button variant="ghost" size="icon" as-child>
                            <Link
                                :href="`/gestao-acessos/permissoes/${row.id}/edit`"
                            >
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
