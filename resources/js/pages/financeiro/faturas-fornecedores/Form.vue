<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

type FaturaData = {
    id?: number;
    numero?: string;
    data_fatura?: string | null;
    data_vencimento?: string | null;
    entidade_id?: number | null;
    encomenda_fornecedor_id?: number | null;
    valor_total?: number | string;
    caminho_documento?: string | null;
    caminho_comprovativo?: string | null;
    estado?: string;
};

const props = defineProps<{
    fatura?: FaturaData;
    fornecedores: FornecedorOption[];
    encomendas_fornecedor: EncomendaOption[];
}>();

const isEditing = computed(() => Boolean(props.fatura?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    { title: 'Faturas Fornecedores', href: '/financeiro/faturas-fornecedores' },
    { title: isEditing.value ? 'Editar' : 'Nova', href: '#' },
];

const form = useForm({
    numero: props.fatura?.numero ?? '',
    data_fatura: props.fatura?.data_fatura ?? '',
    data_vencimento: props.fatura?.data_vencimento ?? '',
    entidade_id: (props.fatura?.entidade_id ?? null) as number | null,
    encomenda_fornecedor_id: (props.fatura?.encomenda_fornecedor_id ?? null) as
        | number
        | null,
    valor_total: props.fatura?.valor_total ?? '',
    estado: props.fatura?.estado ?? 'pendente',
    documento: null as File | null,
    comprovativo: null as File | null,
});

const encomendasFiltradas = computed(() =>
    form.entidade_id
        ? props.encomendas_fornecedor.filter(
              (e) => e.entidade_id === form.entidade_id,
          )
        : props.encomendas_fornecedor,
);

watch(
    () => form.entidade_id,
    () => {
        form.encomenda_fornecedor_id = null;
    },
);

const showComprativoAlert = computed(
    () =>
        isEditing.value &&
        props.fatura?.estado !== 'paga' &&
        form.estado === 'paga',
);

function submit(): void {
    if (isEditing.value) {
        form.post(`/financeiro/faturas-fornecedores/${props.fatura!.id}`, {
            forceFormData: true,
        });
    } else {
        form.post('/financeiro/faturas-fornecedores', {
            forceFormData: true,
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl space-y-6 p-6">
            <h1 class="text-xl font-semibold">
                {{ isEditing ? 'Editar Fatura' : 'Nova Fatura' }}
            </h1>

            <div
                v-if="showComprativoAlert"
                class="rounded-md border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800"
            >
                <strong>Atenção:</strong> Ao marcar como Paga, se tiver um
                comprovativo anexado, será enviado automaticamente um email ao
                fornecedor.
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <!-- Número -->
                <div class="space-y-1">
                    <Label for="numero">Número *</Label>
                    <Input
                        id="numero"
                        v-model="form.numero"
                        placeholder="Ex: FT2026/001"
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

                <!-- Encomenda Fornecedor -->
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

                <!-- Datas -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="data_fatura">Data da Fatura *</Label>
                        <Input
                            id="data_fatura"
                            v-model="form.data_fatura"
                            type="date"
                        />
                        <InputError :message="form.errors.data_fatura" />
                    </div>
                    <div class="space-y-1">
                        <Label for="data_vencimento">Data de Vencimento</Label>
                        <Input
                            id="data_vencimento"
                            v-model="form.data_vencimento"
                            type="date"
                        />
                        <InputError :message="form.errors.data_vencimento" />
                    </div>
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

                <!-- Estado -->
                <div class="space-y-1">
                    <Label>Estado *</Label>
                    <Select v-model="form.estado">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pendente"
                                >Pendente de Pagamento</SelectItem
                            >
                            <SelectItem value="paga">Paga</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.estado" />
                </div>

                <!-- Documento -->
                <div class="space-y-1">
                    <Label for="documento">Documento (fatura)</Label>
                    <Input
                        id="documento"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        @change="
                            (e: Event) =>
                                (form.documento =
                                    (e.target as HTMLInputElement).files?.[0] ??
                                    null)
                        "
                    />
                    <p
                        v-if="fatura?.caminho_documento"
                        class="text-xs text-muted-foreground"
                    >
                        Ficheiro atual guardado. Selecione novo para substituir.
                    </p>
                    <InputError :message="form.errors.documento" />
                </div>

                <!-- Comprovativo -->
                <div class="space-y-1">
                    <Label for="comprovativo">Comprovativo de Pagamento</Label>
                    <Input
                        id="comprovativo"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        @change="
                            (e: Event) =>
                                (form.comprovativo =
                                    (e.target as HTMLInputElement).files?.[0] ??
                                    null)
                        "
                    />
                    <p
                        v-if="fatura?.caminho_comprovativo"
                        class="text-xs text-muted-foreground"
                    >
                        Ficheiro atual guardado. Selecione novo para substituir.
                    </p>
                    <InputError :message="form.errors.comprovativo" />
                </div>

                <div class="flex gap-3 pt-2">
                    <Button type="submit" :disabled="form.processing">
                        {{ isEditing ? 'Guardar' : 'Criar' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <a href="/financeiro/faturas-fornecedores">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
