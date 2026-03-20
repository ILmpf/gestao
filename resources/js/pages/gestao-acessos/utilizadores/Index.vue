<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Utilizador = {
    id: number;
    name: string;
    email: string;
    telemovel: string | null;
    role: string | null;
    estado: 'ativo' | 'inativo';
};

defineProps<{ utilizadores: Utilizador[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestão de Acessos', href: '/gestao-acessos/utilizadores' },
    { title: 'Utilizadores', href: '/gestao-acessos/utilizadores' },
];

const columns: ColumnDef<Utilizador>[] = [
    {
        accessorKey: 'name',
        header: 'Nome',
    },
    {
        accessorKey: 'email',
        header: 'Email',
    },
    {
        accessorKey: 'telemovel',
        header: 'Telemóvel',
        cell: ({ row }) => row.original.telemovel ?? '—',
    },
    {
        accessorKey: 'role',
        header: 'Grupo de Permissões',
        cell: ({ row }) => row.original.role ?? '—',
    },
    {
        accessorKey: 'estado',
        header: 'Estado',
        cell: ({ row }) => row.original.estado,
    },
];

function destroy(utilizador: Utilizador): void {
    if (
        confirm(
            `Eliminar "${utilizador.name}"? Esta ação não pode ser desfeita.`,
        )
    ) {
        router.delete(`/gestao-acessos/utilizadores/${utilizador.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Utilizadores</h1>
                <Button as-child>
                    <Link href="/gestao-acessos/utilizadores/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Novo Utilizador
                    </Link>
                </Button>
            </div>

            <DataTable :data="utilizadores" :columns="columns">
                <template #cell-estado="{ value }">
                    <Badge
                        :variant="value === 'ativo' ? 'default' : 'secondary'"
                    >
                        {{ value === 'ativo' ? 'Ativo' : 'Inativo' }}
                    </Badge>
                </template>
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button variant="ghost" size="icon" as-child>
                            <Link
                                :href="`/gestao-acessos/utilizadores/${row.id}/edit`"
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
