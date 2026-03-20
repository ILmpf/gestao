<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    FileText,
    ShoppingCart,
    TrendingUp,
    Truck,
    Users,
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Stats = {
    clientes_ativos: number;
    fornecedores_ativos: number;
    encomendas_em_progresso: number;
    encomendas_concluidas_mes: number;
    faturas_clientes_pendentes: number;
    faturas_clientes_pendentes_valor: number;
    faturas_clientes_mes_valor: number;
    faturas_fornecedores_pendentes: number;
    faturas_fornecedores_pendentes_valor: number;
    propostas_abertas: number;
};

type Encomenda = {
    id: number;
    numero: number;
    data: string;
    entidade: string;
    estado: string;
};

type Fatura = {
    id: number;
    numero: number;
    data: string;
    entidade: string;
    valor_total: number;
    estado: string;
};

defineProps<{
    stats: Stats;
    ultimasEncomendas: Encomenda[];
    ultimasFaturas: Fatura[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
];

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(value);
}

const estadoEncomendaClass: Record<string, string> = {
    em_progresso:
        'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    concluida:
        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    cancelada: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
};

const estadoEncomendaLabel: Record<string, string> = {
    em_progresso: 'Em Progresso',
    concluida: 'Concluída',
    cancelada: 'Cancelada',
};

const estadoFaturaClass: Record<string, string> = {
    pendente:
        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    paga: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
};

const estadoFaturaLabel: Record<string, string> = {
    pendente: 'Pendente',
    paga: 'Paga',
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Row 1: 4 KPI cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >Clientes Ativos</CardTitle
                        >
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">
                            {{ stats.clientes_ativos }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ stats.fornecedores_ativos }} fornecedores ativos
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >Encomendas em Progresso</CardTitle
                        >
                        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">
                            {{ stats.encomendas_em_progresso }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ stats.encomendas_concluidas_mes }} concluídas
                            este mês
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >Faturas Clientes Pendentes</CardTitle
                        >
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">
                            {{ stats.faturas_clientes_pendentes }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{
                                formatCurrency(
                                    stats.faturas_clientes_pendentes_valor,
                                )
                            }}
                            por receber
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >Faturas Fornecedores Pendentes</CardTitle
                        >
                        <Truck class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">
                            {{ stats.faturas_fornecedores_pendentes }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{
                                formatCurrency(
                                    stats.faturas_fornecedores_pendentes_valor,
                                )
                            }}
                            a pagar
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Row 2: 3 secondary stat cards -->
            <div class="grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >Faturação Este Mês</CardTitle
                        >
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">
                            {{
                                formatCurrency(stats.faturas_clientes_mes_valor)
                            }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Total faturado a clientes
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >Propostas em Aberto</CardTitle
                        >
                        <AlertCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">
                            {{ stats.propostas_abertas }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Aguardam resposta
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                            >Encomendas Concluídas</CardTitle
                        >
                        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">
                            {{ stats.encomendas_concluidas_mes }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Este mês
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Row 3: Recent records -->
            <div class="grid gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <CardTitle class="text-base font-semibold"
                            >Últimas Encomendas</CardTitle
                        >
                        <Link
                            href="/encomendas/clientes"
                            class="text-xs text-muted-foreground hover:underline"
                            >Ver todas</Link
                        >
                    </CardHeader>
                    <CardContent class="p-0">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b text-left text-xs text-muted-foreground"
                                >
                                    <th class="px-6 py-2 font-medium">Nº</th>
                                    <th class="px-6 py-2 font-medium">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-2 font-medium">Data</th>
                                    <th class="px-6 py-2 font-medium">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="enc in ultimasEncomendas"
                                    :key="enc.id"
                                    class="border-b last:border-0 hover:bg-muted/50"
                                >
                                    <td class="px-6 py-3">
                                        <Link
                                            :href="`/encomendas/clientes/${enc.id}/edit`"
                                            class="font-medium hover:underline"
                                        >
                                            #{{ enc.numero }}
                                        </Link>
                                    </td>
                                    <td
                                        class="max-w-[140px] truncate px-6 py-3"
                                    >
                                        {{ enc.entidade }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        {{ enc.data }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            :class="[
                                                'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                                estadoEncomendaClass[
                                                    enc.estado
                                                ] ?? '',
                                            ]"
                                        >
                                            {{
                                                estadoEncomendaLabel[
                                                    enc.estado
                                                ] ?? enc.estado
                                            }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="ultimasEncomendas.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-8 text-center text-muted-foreground"
                                    >
                                        Sem encomendas
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <CardTitle class="text-base font-semibold"
                            >Últimas Faturas de Clientes</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="p-0">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b text-left text-xs text-muted-foreground"
                                >
                                    <th class="px-6 py-2 font-medium">Nº</th>
                                    <th class="px-6 py-2 font-medium">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-2 font-medium">Valor</th>
                                    <th class="px-6 py-2 font-medium">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="fat in ultimasFaturas"
                                    :key="fat.id"
                                    class="border-b last:border-0 hover:bg-muted/50"
                                >
                                    <td class="px-6 py-3 font-medium">
                                        #{{ fat.numero }}
                                    </td>
                                    <td
                                        class="max-w-[140px] truncate px-6 py-3"
                                    >
                                        {{ fat.entidade }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        {{ formatCurrency(fat.valor_total) }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            :class="[
                                                'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                                estadoFaturaClass[fat.estado] ??
                                                    '',
                                            ]"
                                        >
                                            {{
                                                estadoFaturaLabel[fat.estado] ??
                                                fat.estado
                                            }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="ultimasFaturas.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-8 text-center text-muted-foreground"
                                    >
                                        Sem faturas
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
