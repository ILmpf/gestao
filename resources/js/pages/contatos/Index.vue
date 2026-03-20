<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Contato = {
    id: number;
    numero: number | null;
    entidade_id: number;
    entidade_nome: string | null;
    primeiro_nome: string;
    apelido: string;
    funcao_contacto_id: number | null;
    funcao_nome: string | null;
    telefone: string | null;
    telemovel: string | null;
    email: string | null;
    estado: 'ativo' | 'inativo';
};

defineProps<{ contatos: Contato[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Contatos', href: '/contactos' },
];

const columns: ColumnDef<Contato>[] = [
    {
        accessorKey: 'primeiro_nome',
        header: 'Nome',
    },
    {
        accessorKey: 'apelido',
        header: 'Apelido',
    },
    {
        accessorKey: 'funcao_nome',
        header: 'Função',
        cell: ({ row }) => row.original.funcao_nome ?? '—',
    },
    {
        accessorKey: 'entidade_nome',
        header: 'Entidade',
        cell: ({ row }) => row.original.entidade_nome ?? '—',
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
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => row.original.email ?? '—',
    },
];

function destroy(contato: Contato): void {
    if (
        confirm(
            `Eliminar "${contato.primeiro_nome} ${contato.apelido}"? Esta ação não pode ser desfeita.`,
        )
    ) {
        router.delete(`/contactos/${contato.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Contatos</h1>
                <Button as-child>
                    <Link href="/contactos/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Novo
                    </Link>
                </Button>
            </div>

            <DataTable :data="contatos" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="`/contactos/${row.id}/edit`">
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
