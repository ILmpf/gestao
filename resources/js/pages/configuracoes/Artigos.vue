<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Artigo = {
    id: number;
    referencia: string;
    nome: string;
    descricao: string | null;
    preco: number;
    taxa_iva_nome: string | null;
    imagem_url: string | null;
    estado: 'ativo' | 'inativo';
};

defineProps<{ artigos: Artigo[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Configurações', href: '/configuracoes' },
    { title: 'Artigos', href: '/configuracoes/artigos' },
];

const columns: ColumnDef<Artigo>[] = [
    {
        accessorKey: 'referencia',
        header: 'Referência',
    },
    {
        accessorKey: 'imagem_url',
        header: 'Foto',
        enableSorting: false,
        cell: ({ row }) => {
            if (!row.original.imagem_url) {
                return h('span', { class: 'text-muted-foreground' }, '—');
            }
            return h('img', {
                src: row.original.imagem_url,
                alt: row.original.nome,
                class: 'h-10 w-10 rounded object-cover border',
            });
        },
    },
    {
        accessorKey: 'nome',
        header: 'Nome',
    },
    {
        accessorKey: 'descricao',
        header: 'Descrição',
        cell: ({ row }) => {
            const desc = row.original.descricao;
            if (!desc) return '—';
            return desc.length > 60 ? desc.slice(0, 60) + '…' : desc;
        },
    },
    {
        accessorKey: 'taxa_iva_nome',
        header: 'IVA',
        cell: ({ row }) => row.original.taxa_iva_nome ?? '—',
    },
    {
        accessorKey: 'preco',
        header: 'Preço',
        cell: ({ row }) =>
            new Intl.NumberFormat('pt-PT', {
                style: 'currency',
                currency: 'EUR',
            }).format(Number(row.original.preco)),
    },
];

function destroy(artigo: Artigo): void {
    if (
        confirm(`Eliminar "${artigo.nome}"? Esta ação não pode ser desfeita.`)
    ) {
        router.delete(`/configuracoes/artigos/${artigo.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Artigos</h1>
                <Button as-child>
                    <Link href="/configuracoes/artigos/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Novo
                    </Link>
                </Button>
            </div>

            <DataTable :data="artigos" :columns="columns">
                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button variant="ghost" size="icon" as-child>
                            <Link
                                :href="`/configuracoes/artigos/${row.id}/edit`"
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
