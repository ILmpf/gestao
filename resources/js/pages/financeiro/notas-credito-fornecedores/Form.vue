<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type FornecedorOption = { id: number; nome: string };
type EncomendaOption = { id: number; numero: string; entidade_id: number };
type FaturaOption = { id: number; numero: string; entidade_id: number };

type NotaData = {
    id?: number;
    numero?: string;
    data_nota_credito?: string | null;
    entidade_id?: number | null;
    encomenda_fornecedor_id?: number | null;
    fatura_fornecedor_id?: number | null;
    valor_total?: number | string;
    motivo?: string | null;
    estado?: string;
};

const props = defineProps<{
    nota?: NotaData;
    fornecedores: FornecedorOption[];
    encomendas_fornecedor: EncomendaOption[];
    faturas_fornecedor: FaturaOption[];
}>();

const isEditing = computed(() => Boolean(props.nota?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    {
        title: 'Notas de Crédito Fornecedores',
        href: '/financeiro/notas-credito-fornecedores',
    },
    { title: isEditing.value ? 'Editar' : 'Nova', href: '#' },
];

const form = useForm({
    numero: props.nota?.numero ?? '',
    data_nota_credito: props.nota?.data_nota_credito ?? '',
    entidade_id: (props.nota?.entidade_id ?? null) as number | null,
    encomenda_fornecedor_id: (props.nota?.encomenda_fornecedor_id ?? null) as
        | number
        | null,
    fatura_fornecedor_id: (props.nota?.fatura_fornecedor_id ?? null) as
        | number
        | null,
    valor_total: props.nota?.valor_total ?? '',
    motivo: props.nota?.motivo ?? '',
    estado: props.nota?.estado ?? 'pendente',
});

const encomendasFiltradas = computed(() =>
    form.entidade_id
        ? props.encomendas_fornecedor.filter(
              (e) => e.entidade_id === form.entidade_id,
          )
        : props.encomendas_fornecedor,
);

const faturasFiltradas = computed(() =>
    form.entidade_id
        ? props.faturas_fornecedor.filter(
              (f) => f.entidade_id === form.entidade_id,
          )
        : props.faturas_fornecedor,
);

watch(
    () => form.entidade_id,
    () => {
        form.encomenda_fornecedor_id = null;
        form.fatura_fornecedor_id = null;
    },
);

function submit(): void {
    if (isEditing.value) {
        form.put(`/financeiro/notas-credito-fornecedores/${props.nota!.id}`);
    } else {
        form.post('/financeiro/notas-credito-fornecedores');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl space-y-6 p-6">
            <h1 class="text-xl font-semibold">
                {{
                    isEditing
                        ? 'Editar Nota de Crédito'
                        : 'Nova Nota de Crédito'
                }}
            </h1>

            <form class="space-y-4" @submit.prevent="submit">
                <!-- Número -->
                <div class="space-y-1">
                    <Label for="numero">Número *</Label>
                    <Input
                        id="numero"
                        v-model="form.numero"
                        placeholder="Ex: NC2026/001"
                    />
                    <InputError :message="form.errors.numero" />
                </div>

                <!-- Fornecedor -->
                <div class="space-y-1">
                    <Label>Fornecedor *</Label>
                    <Select
                        :model-value="form.entidade_id?.toString() ?? ''"
                        @update:model-value="
                            (v) => (form.entidade_id = v ? Number(v) : null)
                        "
                    >
                        <SelectTrigger
                            ><SelectValue placeholder="Selecione o fornecedor"
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="f in fornecedores"
                                :key="f.id"
                                :value="f.id.toString()"
                            >
                                {{ f.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.entidade_id" />
                </div>

                <!-- Fatura Associada -->
                <div class="space-y-1">
                    <Label>Fatura Associada</Label>
                    <Select
                        :model-value="
                            form.fatura_fornecedor_id?.toString() ?? ''
                        "
                        @update:model-value="
                            (v) =>
                                (form.fatura_fornecedor_id = v
                                    ? Number(v)
                                    : null)
                        "
                    >
                        <SelectTrigger
                            ><SelectValue
                                placeholder="Associar fatura (opcional)"
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="f in faturasFiltradas"
                                :key="f.id"
                                :value="f.id.toString()"
                            >
                                {{ f.numero }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.fatura_fornecedor_id" />
                </div>

                <!-- Encomenda -->
                <div class="space-y-1">
                    <Label>Encomenda Fornecedor</Label>
                    <Select
                        :model-value="
                            form.encomenda_fornecedor_id?.toString() ?? ''
                        "
                        @update:model-value="
                            (v) =>
                                (form.encomenda_fornecedor_id = v
                                    ? Number(v)
                                    : null)
                        "
                    >
                        <SelectTrigger
                            ><SelectValue
                                placeholder="Associar encomenda (opcional)"
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="e in encomendasFiltradas"
                                :key="e.id"
                                :value="e.id.toString()"
                            >
                                {{ e.numero }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        :message="form.errors.encomenda_fornecedor_id"
                    />
                </div>

                <!-- Data -->
                <div class="space-y-1">
                    <Label for="data_nota_credito"
                        >Data da Nota de Crédito *</Label
                    >
                    <Input
                        id="data_nota_credito"
                        v-model="form.data_nota_credito"
                        type="date"
                    />
                    <InputError :message="form.errors.data_nota_credito" />
                </div>

                <!-- Valor Total -->
                <div class="space-y-1">
                    <Label for="valor_total">Valor Total *</Label>
                    <Input
                        id="valor_total"
                        v-model="form.valor_total"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                    <InputError :message="form.errors.valor_total" />
                </div>

                <!-- Motivo -->
                <div class="space-y-1">
                    <Label for="motivo">Motivo</Label>
                    <Textarea
                        id="motivo"
                        v-model="form.motivo"
                        rows="3"
                        placeholder="Descreva o motivo da nota de crédito..."
                    />
                    <InputError :message="form.errors.motivo" />
                </div>

                <!-- Estado -->
                <div class="space-y-1">
                    <Label>Estado *</Label>
                    <Select v-model="form.estado">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pendente">Pendente</SelectItem>
                            <SelectItem value="processada"
                                >Processada</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.estado" />
                </div>

                <div class="flex gap-3 pt-2">
                    <Button type="submit" :disabled="form.processing">
                        {{ isEditing ? 'Guardar' : 'Criar' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <a href="/financeiro/notas-credito-fornecedores"
                            >Cancelar</a
                        >
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
