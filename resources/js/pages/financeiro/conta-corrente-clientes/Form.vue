<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
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

type EntidadeOption = { id: number; nome: string };

type MovimentoData = {
    id?: number;
    entidade_id?: number | null;
    tipo_lancamento?: string;
    valor?: number | string;
    descricao?: string;
    data?: string | null;
};

const props = defineProps<{
    entidade: EntidadeOption;
    movimento?: MovimentoData;
}>();

const isEditing = computed(() => Boolean(props.movimento?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    {
        title: 'Conta Corrente Clientes',
        href: '/financeiro/conta-corrente-clientes',
    },
    {
        title: props.entidade.nome,
        href: `/financeiro/conta-corrente-clientes/${props.entidade.id}`,
    },
    { title: isEditing.value ? 'Editar' : 'Novo Movimento', href: '#' },
];

const tiposLancamento = [
    { value: 'fatura', label: 'Fatura' },
    { value: 'nota_credito', label: 'Nota de Crédito' },
    { value: 'pagamento', label: 'Pagamento' },
];

const form = useForm({
    entidade_id: props.entidade.id,
    tipo_lancamento: props.movimento?.tipo_lancamento ?? '',
    valor: props.movimento?.valor ?? '0',
    descricao: props.movimento?.descricao ?? '',
    data_lancamento:
        props.movimento?.data ?? new Date().toISOString().slice(0, 10),
});

function submit(): void {
    if (isEditing.value) {
        form.put(`/financeiro/conta-corrente-clientes/${props.movimento!.id}`);
    } else {
        form.post('/financeiro/conta-corrente-clientes');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-xl space-y-6 p-6">
            <h1 class="text-xl font-semibold">
                {{ isEditing ? 'Editar Movimento' : 'Novo Movimento' }}
            </h1>

            <form class="space-y-4" @submit.prevent="submit">
                <!-- Cliente -->
                <div class="space-y-1">
                    <Label>Cliente</Label>
                    <div
                        class="rounded-md border bg-muted/40 px-3 py-2 text-sm text-muted-foreground"
                    >
                        {{ entidade.nome }}
                    </div>
                </div>

                <!-- Tipo de Lançamento -->
                <div class="space-y-1">
                    <Label>Tipo de Lançamento *</Label>
                    <Select
                        :model-value="form.tipo_lancamento"
                        @update:model-value="(v) => (form.tipo_lancamento = v)"
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Selecione o tipo" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="t in tiposLancamento"
                                :key="t.value"
                                :value="t.value"
                            >
                                {{ t.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.tipo_lancamento" />
                </div>

                <!-- Valor -->
                <div class="space-y-1">
                    <Label for="valor">Valor *</Label>
                    <Input
                        id="valor"
                        v-model="form.valor"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                    />
                    <InputError :message="form.errors.valor" />
                </div>

                <!-- Descrição -->
                <div class="space-y-1">
                    <Label for="descricao">Descrição *</Label>
                    <Input
                        id="descricao"
                        v-model="form.descricao"
                        placeholder="Ex: Pagamento fatura #001"
                    />
                    <InputError :message="form.errors.descricao" />
                </div>

                <!-- Data -->
                <div class="space-y-1">
                    <Label for="data_lancamento">Data *</Label>
                    <Input
                        id="data_lancamento"
                        v-model="form.data_lancamento"
                        type="date"
                    />
                    <InputError :message="form.errors.data_lancamento" />
                </div>

                <div class="flex gap-3 pt-2">
                    <Button type="submit" :disabled="form.processing">
                        {{ isEditing ? 'Guardar' : 'Criar' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <a
                            :href="`/financeiro/conta-corrente-clientes/${entidade.id}`"
                            >Cancelar</a
                        >
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
