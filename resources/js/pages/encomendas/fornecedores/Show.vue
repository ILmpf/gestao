<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Linha = {
    artigo_referencia: string | null;
    artigo_nome: string | null;
    quantidade: number;
    preco_unitario: number;
    subtotal: number;
};

type Encomenda = {
    id: number;
    numero: string;
    data_encomenda: string | null;
    entidade_nome: string | null;
    encomenda_cliente_num: string | null;
    valor_total: number;
    estado: string;
    linhas: Linha[];
};

defineProps<{ encomenda: Encomenda }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Encomendas', href: '/encomendas' },
    { title: 'Fornecedores', href: '/encomendas/fornecedores' },
    { title: 'Detalhe', href: '#' },
];

const fmtEur = (v: number) =>
    new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(v);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">
                    Encomenda {{ encomenda.numero }}
                </h1>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <a
                            :href="`/encomendas/fornecedores/${encomenda.id}/pdf`"
                            target="_blank"
                        >
                            Exportar PDF
                        </a>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/encomendas/fornecedores">Voltar</Link>
                    </Button>
                </div>
            </div>

            <div
                class="grid grid-cols-2 gap-4 rounded-md border p-4 text-sm md:grid-cols-4"
            >
                <div>
                    <p
                        class="text-xs font-semibold text-muted-foreground uppercase"
                    >
                        Fornecedor
                    </p>
                    <p>{{ encomenda.entidade_nome ?? '—' }}</p>
                </div>
                <div>
                    <p
                        class="text-xs font-semibold text-muted-foreground uppercase"
                    >
                        Data
                    </p>
                    <p>{{ encomenda.data_encomenda ?? '—' }}</p>
                </div>
                <div>
                    <p
                        class="text-xs font-semibold text-muted-foreground uppercase"
                    >
                        Estado
                    </p>
                    <p>{{ encomenda.estado }}</p>
                </div>
                <div v-if="encomenda.encomenda_cliente_num">
                    <p
                        class="text-xs font-semibold text-muted-foreground uppercase"
                    >
                        Enc. Cliente Origem
                    </p>
                    <p>{{ encomenda.encomenda_cliente_num }}</p>
                </div>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Ref.</TableHead>
                            <TableHead>Designação</TableHead>
                            <TableHead class="text-right">Qtd.</TableHead>
                            <TableHead class="text-right">P. Unit.</TableHead>
                            <TableHead class="text-right">Subtotal</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="(linha, i) in encomenda.linhas"
                            :key="i"
                        >
                            <TableCell class="font-mono text-xs">{{
                                linha.artigo_referencia ?? '—'
                            }}</TableCell>
                            <TableCell>{{
                                linha.artigo_nome ?? '—'
                            }}</TableCell>
                            <TableCell class="text-right">{{
                                linha.quantidade
                            }}</TableCell>
                            <TableCell class="text-right">{{
                                fmtEur(Number(linha.preco_unitario))
                            }}</TableCell>
                            <TableCell class="text-right font-medium">{{
                                fmtEur(Number(linha.subtotal))
                            }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-end">
                <div class="rounded-md border px-6 py-3 text-right">
                    <p class="text-sm text-muted-foreground">Total</p>
                    <p class="text-xl font-bold">
                        {{ fmtEur(encomenda.valor_total) }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
