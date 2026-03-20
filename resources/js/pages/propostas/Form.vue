<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Loader2, Plus, Trash2, Search } from 'lucide-vue-next';
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
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ClienteOption = { id: number; nome: string };
type FornecedorOption = { id: number; nome: string };
type ArtigoOption = {
    id: number;
    referencia: string;
    nome: string;
    preco: number;
    taxa_iva_id: number | null;
};
type TaxaOption = { id: number; nome: string; taxa: number };

type Linha = {
    artigo_id: number | null;
    artigo_nome: string;
    entidade_fornecedor_id: number | null;
    taxa_iva_id: number | null;
    quantidade: number | string;
    preco_unitario: number | string;
    preco_custo: number | string;
};

type PropostaData = {
    id?: number;
    entidade_id?: number | null;
    data_proposta?: string | null;
    validade?: string | null;
    estado?: string;
    linhas?: Linha[];
};

const props = defineProps<{
    proposta?: PropostaData;
    clientes: ClienteOption[];
    fornecedores: FornecedorOption[];
    artigos: ArtigoOption[];
    taxas: TaxaOption[];
}>();

const isEditing = computed(() => Boolean(props.proposta?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Propostas', href: '/propostas' },
    { title: isEditing.value ? 'Editar' : 'Nova', href: '#' },
];

const defaultValidade = (() => {
    const d = new Date();
    d.setDate(d.getDate() + 30);
    return d.toISOString().slice(0, 10);
})();

const form = useForm({
    entidade_id: (props.proposta?.entidade_id ?? null) as number | null,
    data_proposta: props.proposta?.data_proposta ?? null,
    validade: props.proposta?.validade ?? defaultValidade,
    estado: props.proposta?.estado ?? 'apresentada',
    linhas: (props.proposta?.linhas ?? []).map((l) => ({
        artigo_id: l.artigo_id,
        artigo_nome: l.artigo_nome ?? '',
        entidade_fornecedor_id: l.entidade_fornecedor_id,
        taxa_iva_id: l.taxa_iva_id,
        quantidade: l.quantidade,
        preco_unitario: l.preco_unitario,
        preco_custo: l.preco_custo ?? '',
    })) as Linha[],
});

// Procurar Artigo
const openPopover = ref<number | null>(null);

function addLinha(): void {
    form.linhas.push({
        artigo_id: null,
        artigo_nome: '',
        entidade_fornecedor_id: null,
        taxa_iva_id: null,
        quantidade: 1,
        preco_unitario: 0,
        preco_custo: '',
    });
}

function removeLinha(i: number): void {
    form.linhas.splice(i, 1);
}

function selectArtigo(i: number, artigo: ArtigoOption): void {
    form.linhas[i].artigo_id = artigo.id;
    form.linhas[i].artigo_nome = `[${artigo.referencia}] ${artigo.nome}`;
    form.linhas[i].preco_unitario = artigo.preco;
    form.linhas[i].taxa_iva_id = artigo.taxa_iva_id;
    openPopover.value = null;
}

const subtotal = (linha: Linha) =>
    Math.round(Number(linha.quantidade) * Number(linha.preco_unitario) * 100) /
    100;

const total = computed(() => form.linhas.reduce((s, l) => s + subtotal(l), 0));

const fmtEur = (v: number) =>
    new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(v);

function submit(): void {
    form.transform((data) => ({
        ...data,
        linhas: data.linhas.map((l) => ({
            artigo_id: l.artigo_id,
            entidade_fornecedor_id: l.entidade_fornecedor_id,
            taxa_iva_id: l.taxa_iva_id,
            quantidade: l.quantidade,
            preco_unitario: l.preco_unitario,
            preco_custo: l.preco_custo === '' ? null : l.preco_custo,
            subtotal: subtotal(l),
        })),
    }));
    if (isEditing.value && props.proposta?.id) {
        form.put(`/propostas/${props.proposta.id}`);
    } else {
        form.post('/propostas');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl p-6">
            <h1 class="mb-6 text-xl font-semibold">
                {{ isEditing ? 'Editar' : 'Nova' }} Proposta
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
                <!-- Header fields -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <!-- Cliente -->
                    <div class="col-span-2 space-y-2">
                        <Label for="entidade_id"
                            >Cliente
                            <span class="text-destructive">*</span></Label
                        >
                        <Select
                            :model-value="
                                form.entidade_id ? String(form.entidade_id) : ''
                            "
                            @update:model-value="
                                (v: string) =>
                                    (form.entidade_id = v ? Number(v) : null)
                            "
                        >
                            <SelectTrigger id="entidade_id">
                                <SelectValue
                                    placeholder="Selecione o cliente"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="c in clientes"
                                    :key="c.id"
                                    :value="String(c.id)"
                                >
                                    {{ c.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.entidade_id" />
                    </div>

                    <!-- Data Proposta -->
                    <div class="space-y-2">
                        <Label for="data_proposta">Data</Label>
                        <Input
                            id="data_proposta"
                            v-model="form.data_proposta"
                            type="date"
                        />
                        <InputError :message="form.errors.data_proposta" />
                    </div>

                    <!-- Validade -->
                    <div class="space-y-2">
                        <Label for="validade">Validade</Label>
                        <Input
                            id="validade"
                            v-model="form.validade"
                            type="date"
                        />
                        <InputError :message="form.errors.validade" />
                    </div>

                    <!-- Estado -->
                    <div class="col-span-2 space-y-2 md:col-span-1">
                        <Label for="estado">Estado</Label>
                        <Select v-model="form.estado">
                            <SelectTrigger id="estado">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="apresentada"
                                    >Apresentada</SelectItem
                                >
                                <SelectItem value="concluida"
                                    >Concluída</SelectItem
                                >
                                <SelectItem value="rejeitada"
                                    >Rejeitada</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.estado" />
                    </div>
                </div>

                <!-- Linhas -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Linhas</h2>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="addLinha"
                        >
                            <Plus class="mr-1 h-4 w-4" /> Adicionar linha
                        </Button>
                    </div>
                    <InputError :message="(form.errors as any).linhas" />

                    <div
                        v-if="form.linhas.length === 0"
                        class="rounded-md border p-6 text-center text-sm text-muted-foreground"
                    >
                        Sem linhas. Clique em "Adicionar linha" para começar.
                    </div>

                    <div
                        v-for="(linha, i) in form.linhas"
                        :key="i"
                        class="rounded-md border p-4"
                    >
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12 space-y-1 md:col-span-4">
                                <Label
                                    >Artigo
                                    <span class="text-destructive"
                                        >*</span
                                    ></Label
                                >
                                <Popover
                                    :open="openPopover === i"
                                    @update:open="
                                        (v) => (openPopover = v ? i : null)
                                    "
                                >
                                    <PopoverTrigger as-child>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="w-full justify-start font-normal"
                                        >
                                            <Search
                                                class="mr-2 h-4 w-4 shrink-0 text-muted-foreground"
                                            />
                                            <span class="truncate">
                                                {{
                                                    linha.artigo_nome ||
                                                    'Pesquisar artigo...'
                                                }}
                                            </span>
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent
                                        class="w-80 p-0"
                                        align="start"
                                    >
                                        <Command>
                                            <CommandInput
                                                placeholder="Referência ou nome..."
                                            />
                                            <CommandList>
                                                <CommandEmpty
                                                    >Sem
                                                    resultados.</CommandEmpty
                                                >
                                                <CommandGroup>
                                                    <CommandItem
                                                        v-for="a in artigos"
                                                        :key="a.id"
                                                        :value="`${a.referencia} ${a.nome}`"
                                                        @select="
                                                            selectArtigo(i, a)
                                                        "
                                                    >
                                                        <span
                                                            class="font-mono text-xs text-muted-foreground"
                                                            >{{
                                                                a.referencia
                                                            }}</span
                                                        >
                                                        <span class="ml-2">{{
                                                            a.nome
                                                        }}</span>
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <InputError
                                    :message="
                                        (form.errors as any)[
                                            `linhas.${i}.artigo_id`
                                        ]
                                    "
                                />
                            </div>

                            <!-- Fornecedor -->
                            <div class="col-span-12 space-y-1 md:col-span-3">
                                <Label>Fornecedor</Label>
                                <Select
                                    :model-value="
                                        linha.entidade_fornecedor_id
                                            ? String(
                                                  linha.entidade_fornecedor_id,
                                              )
                                            : ''
                                    "
                                    @update:model-value="
                                        (v: string) =>
                                            (linha.entidade_fornecedor_id = v
                                                ? Number(v)
                                                : null)
                                    "
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="f in fornecedores"
                                            :key="f.id"
                                            :value="String(f.id)"
                                        >
                                            {{ f.nome }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- IVA -->
                            <div class="col-span-4 space-y-1 md:col-span-2">
                                <Label>IVA</Label>
                                <Select
                                    :model-value="
                                        linha.taxa_iva_id
                                            ? String(linha.taxa_iva_id)
                                            : ''
                                    "
                                    @update:model-value="
                                        (v: string) =>
                                            (linha.taxa_iva_id = v
                                                ? Number(v)
                                                : null)
                                    "
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="—" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="t in taxas"
                                            :key="t.id"
                                            :value="String(t.id)"
                                        >
                                            {{ t.nome }} ({{ t.taxa }}%)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Qtd -->
                            <div class="col-span-4 space-y-1 md:col-span-1">
                                <Label>Qtd.</Label>
                                <Input
                                    v-model="linha.quantidade"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                />
                                <InputError
                                    :message="
                                        (form.errors as any)[
                                            `linhas.${i}.quantidade`
                                        ]
                                    "
                                />
                            </div>

                            <!-- Preço Unit. -->
                            <div class="col-span-4 space-y-1 md:col-span-1">
                                <Label>P. Unit.</Label>
                                <Input
                                    v-model="linha.preco_unitario"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                />
                                <InputError
                                    :message="
                                        (form.errors as any)[
                                            `linhas.${i}.preco_unitario`
                                        ]
                                    "
                                />
                            </div>

                            <!-- P. Custo -->
                            <div class="col-span-6 space-y-1 md:col-span-1">
                                <Label>P. Custo</Label>
                                <Input
                                    v-model="linha.preco_custo"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="—"
                                />
                            </div>

                            <!-- Subtotal -->
                            <div
                                class="col-span-6 flex items-end justify-between md:col-span-12"
                            >
                                <span class="text-sm text-muted-foreground">
                                    Subtotal:
                                    <strong>{{
                                        fmtEur(subtotal(linha))
                                    }}</strong>
                                </span>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="text-destructive hover:text-destructive"
                                    @click="removeLinha(i)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div
                        v-if="form.linhas.length > 0"
                        class="flex justify-end pt-2"
                    >
                        <div class="rounded-md border px-6 py-3 text-right">
                            <div class="text-sm text-muted-foreground">
                                Total (s/ IVA)
                            </div>
                            <div class="text-xl font-bold">
                                {{ fmtEur(total) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ isEditing ? 'Atualizar' : 'Criar' }}
                    </Button>
                    <Button type="button" variant="outline" as-child>
                        <a href="/propostas">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
