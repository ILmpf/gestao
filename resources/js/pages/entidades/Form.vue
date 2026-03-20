<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Loader2, Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Pais = { id: number; nome: string; codigo: string };

type EntidadeData = {
    id?: number;
    tipos: string[];
    nif?: string | null;
    nome?: string;
    morada?: string | null;
    codigo_postal?: string | null;
    cidade?: string | null;
    pais_id?: number | null;
    telefone?: string | null;
    telemovel?: string | null;
    website?: string | null;
    email?: string | null;
    notas?: string | null;
    prazo_pagamento_dias?: number | null;
    estado?: string;
};

const props = defineProps<{
    tipo?: 'cliente' | 'fornecedor' | null;
    entidade?: EntidadeData;
    paises: Pais[];
}>();

const isEditing = computed(() => Boolean(props.entidade?.id));
const label =
    props.tipo === 'cliente'
        ? 'Clientes'
        : props.tipo === 'fornecedor'
          ? 'Fornecedores'
          : 'Entidades';
const singularLabel =
    props.tipo === 'cliente'
        ? 'Cliente'
        : props.tipo === 'fornecedor'
          ? 'Fornecedor'
          : 'Entidade';
const newPrefix = props.tipo == null ? 'Nova' : 'Novo';
const baseHref =
    props.tipo === 'cliente'
        ? '/clientes'
        : props.tipo === 'fornecedor'
          ? '/fornecedores'
          : '/entidades';

const breadcrumbs: BreadcrumbItem[] = [
    { title: label, href: baseHref },
    { title: isEditing.value ? 'Editar' : 'Novo', href: '#' },
];

const form = useForm({
    tipos: (props.entidade?.tipos ??
        (props.tipo ? [props.tipo] : [])) as string[],
    nif: props.entidade?.nif ?? '',
    nome: props.entidade?.nome ?? '',
    morada: props.entidade?.morada ?? '',
    codigo_postal: props.entidade?.codigo_postal ?? '',
    cidade: props.entidade?.cidade ?? '',
    pais_id: (props.entidade?.pais_id ?? null) as number | null,
    telefone: props.entidade?.telefone ?? '',
    telemovel: props.entidade?.telemovel ?? '',
    website: props.entidade?.website ?? '',
    email: props.entidade?.email ?? '',
    notas: props.entidade?.notas ?? '',
    prazo_pagamento_dias: (props.entidade?.prazo_pagamento_dias ?? null) as
        | number
        | null,
    estado: props.entidade?.estado ?? 'ativo',
});

function toggleTipo(tipo: string): void {
    const idx = form.tipos.indexOf(tipo);
    if (idx >= 0) {
        form.tipos.splice(idx, 1);
    } else {
        form.tipos.push(tipo);
    }
}

// VIES
const viesLoading = ref(false);
const viesMessage = ref('');
const viesSuccess = ref(false);

async function viesLookup(): Promise<void> {
    if (!form.pais_id || !form.nif) return;

    const pais = props.paises.find((p) => p.id === form.pais_id);
    if (!pais) return;

    viesLoading.value = true;
    viesMessage.value = '';
    viesSuccess.value = false;

    try {
        const resp = await fetch(
            `/vies/lookup?pais=${encodeURIComponent(pais.codigo)}&vat_number=${encodeURIComponent(form.nif)}`,
        );

        if (!resp.ok) {
            throw new Error('Serviço indisponível');
        }

        const data = (await resp.json()) as {
            valid: boolean;
            message?: string;
            nome?: string;
            morada?: string;
        };

        if (data.valid) {
            if (data.nome) form.nome = data.nome;
            if (data.morada) form.morada = data.morada;
            viesSuccess.value = true;
            viesMessage.value = 'Dados preenchidos automaticamente pelo VIES.';
        } else {
            viesMessage.value = data.message ?? 'NIF não encontrado no VIES.';
        }
    } catch {
        viesMessage.value =
            'Erro ao contactar o serviço VIES. Tente novamente.';
    } finally {
        viesLoading.value = false;
    }
}

function submit(): void {
    if (isEditing.value && props.entidade?.id) {
        form.put(`/entidades/${props.entidade.id}`);
    } else {
        form.post('/entidades');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl p-6">
            <h1 class="mb-6 text-xl font-semibold">
                {{ isEditing ? 'Editar' : newPrefix }} {{ singularLabel }}
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
                <!-- Tipo -->
                <div class="space-y-2">
                    <Label>Tipo <span class="text-destructive">*</span></Label>
                    <div class="flex gap-6">
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="tipo-cliente"
                                :model-value="form.tipos.includes('cliente')"
                                @update:model-value="toggleTipo('cliente')"
                            />
                            <label
                                for="tipo-cliente"
                                class="cursor-pointer text-sm"
                                >Cliente</label
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="tipo-fornecedor"
                                :model-value="form.tipos.includes('fornecedor')"
                                @update:model-value="toggleTipo('fornecedor')"
                            />
                            <label
                                for="tipo-fornecedor"
                                class="cursor-pointer text-sm"
                                >Fornecedor</label
                            >
                        </div>
                    </div>
                    <InputError
                        :message="
                            (form.errors as Record<string, string>)['tipos']
                        "
                    />
                </div>

                <!-- País -->
                <div class="space-y-2">
                    <Label for="pais_id">País</Label>
                    <Select
                        :model-value="form.pais_id ? String(form.pais_id) : ''"
                        @update:model-value="
                            (v: string) => (form.pais_id = v ? Number(v) : null)
                        "
                    >
                        <SelectTrigger id="pais_id">
                            <SelectValue placeholder="Selecione o país" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="pais in paises"
                                :key="pais.id"
                                :value="String(pais.id)"
                            >
                                {{ pais.nome }} ({{ pais.codigo }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.pais_id" />
                </div>

                <!-- NIF -->
                <div class="space-y-2">
                    <Label for="nif">NIF</Label>
                    <div class="flex gap-2">
                        <Input
                            id="nif"
                            v-model="form.nif"
                            class="flex-1"
                            placeholder="ex: 500000000"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="
                                !form.nif || !form.pais_id || viesLoading
                            "
                            title="Consultar VIES (requer País e NIF preenchidos)"
                            @click="viesLookup"
                        >
                            <Loader2
                                v-if="viesLoading"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <Search v-else class="mr-2 h-4 w-4" />
                            VIES
                        </Button>
                    </div>
                    <p
                        v-if="viesMessage"
                        class="text-sm"
                        :class="
                            viesSuccess
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-destructive'
                        "
                    >
                        {{ viesMessage }}
                    </p>
                    <InputError :message="form.errors.nif" />
                </div>

                <!-- Nome -->
                <div class="space-y-2">
                    <Label for="nome"
                        >Nome <span class="text-destructive">*</span></Label
                    >
                    <Input
                        id="nome"
                        v-model="form.nome"
                        placeholder="Nome da entidade"
                    />
                    <InputError :message="form.errors.nome" />
                </div>

                <!-- Morada -->
                <div class="space-y-2">
                    <Label for="morada">Morada</Label>
                    <Input
                        id="morada"
                        v-model="form.morada"
                        placeholder="Rua, Nº, Andar"
                    />
                    <InputError :message="form.errors.morada" />
                </div>

                <!-- Código Postal + Localidade -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="codigo_postal">Código Postal</Label>
                        <Input
                            id="codigo_postal"
                            v-model="form.codigo_postal"
                            placeholder="XXXX-XXX"
                        />
                        <InputError :message="form.errors.codigo_postal" />
                    </div>
                    <div class="space-y-2">
                        <Label for="cidade">Localidade</Label>
                        <Input
                            id="cidade"
                            v-model="form.cidade"
                            placeholder="Cidade"
                        />
                        <InputError :message="form.errors.cidade" />
                    </div>
                </div>

                <!-- Telefone + Telemóvel -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="telefone">Telefone</Label>
                        <Input
                            id="telefone"
                            v-model="form.telefone"
                            placeholder="+351 210 000 000"
                        />
                        <InputError :message="form.errors.telefone" />
                    </div>
                    <div class="space-y-2">
                        <Label for="telemovel">Telemóvel</Label>
                        <Input
                            id="telemovel"
                            v-model="form.telemovel"
                            placeholder="+351 910 000 000"
                        />
                        <InputError :message="form.errors.telemovel" />
                    </div>
                </div>

                <!-- Website + Email -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="website">Website</Label>
                        <Input
                            id="website"
                            v-model="form.website"
                            placeholder="https://empresa.pt"
                        />
                        <InputError :message="form.errors.website" />
                    </div>
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="geral@empresa.pt"
                        />
                        <InputError :message="form.errors.email" />
                    </div>
                </div>

                <!-- Observações -->
                <div class="space-y-2">
                    <Label for="notas">Observações</Label>
                    <Textarea
                        id="notas"
                        v-model="form.notas"
                        :rows="4"
                        placeholder="Observações..."
                    />
                    <InputError :message="form.errors.notas" />
                </div>

                <!-- Prazo de Pagamento -->
                <div class="space-y-2">
                    <Label for="prazo_pagamento_dias"
                        >Prazo de Pagamento (dias)</Label
                    >
                    <Input
                        id="prazo_pagamento_dias"
                        v-model.number="form.prazo_pagamento_dias"
                        type="number"
                        min="0"
                        max="365"
                        placeholder="Ex: 30"
                    />
                    <InputError :message="form.errors.prazo_pagamento_dias" />
                </div>

                <!-- Estado -->
                <div class="space-y-2">
                    <Label for="estado">Estado</Label>
                    <Select v-model="form.estado">
                        <SelectTrigger id="estado">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="ativo">Ativo</SelectItem>
                            <SelectItem value="inativo">Inativo</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.estado" />
                </div>

                <!-- Botões -->
                <div class="flex gap-3 pt-2">
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ isEditing ? 'Atualizar' : 'Criar' }}
                    </Button>
                    <Button type="button" variant="outline" as-child>
                        <a :href="baseHref">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
