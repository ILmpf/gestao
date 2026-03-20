<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Loader2, Plus, Search, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
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
};

type EncomendaData = {
    id?: number;
    entidade_id?: number | null;
    data_encomenda?: string | null;
    estado?: string;
    linhas?: Linha[];
};

const props = defineProps<{
    encomenda?: EncomendaData;
    clientes: ClienteOption[];
    fornecedores: FornecedorOption[];
    artigos: ArtigoOption[];
    taxas: TaxaOption[];
}>();

const isEditing = computed(() => Boolean(props.encomenda?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Encomendas', href: '/encomendas' },
    { title: 'Clientes', href: '/encomendas/clientes' },
    { title: isEditing.value ? 'Editar' : 'Nova', href: '#' },
];

const form = useForm({
    entidade_id: (props.encomenda?.entidade_id ?? null) as number | null,
    data_encomenda: props.encomenda?.data_encomenda ?? null,
    estado: props.encomenda?.estado ?? 'em_progresso',
    linhas: (props.encomenda?.linhas ?? []).map((l) => {
        const artigo = l.artigo_id
            ? props.artigos.find((a) => a.id === l.artigo_id)
            : null;
        const artigo_nome = artigo
            ? `[${artigo.referencia}] ${artigo.nome}`
            : (l.artigo_nome ?? '');
        return {
            artigo_id: l.artigo_id,
            artigo_nome,
            entidade_fornecedor_id: l.entidade_fornecedor_id,
            taxa_iva_id: l.taxa_iva_id,
            quantidade: l.quantidade,
            preco_unitario: l.preco_unitario,
        };
    }) as Linha[],
});

const openPopover = ref<number | null>(null);

function addLinha(): void {
    form.linhas.push({
        artigo_id: null,
        artigo_nome: '',
        entidade_fornecedor_id: null,
        taxa_iva_id: null,
        quantidade: 1,
        preco_unitario: 0,
    });
}

function removeLinha(i: number): void {
    form.linhas.splice(i, 1);
}

function selectArtigo(i: number, a: ArtigoOption): void {
    form.linhas[i].artigo_id = a.id;
    form.linhas[i].artigo_nome = `[${a.referencia}] ${a.nome}`;
    form.linhas[i].preco_unitario = a.preco;
    form.linhas[i].taxa_iva_id = a.taxa_iva_id;
    openPopover.value = null;
}

const subtotal = (l: Linha) =>
    Math.round(Number(l.quantidade) * Number(l.preco_unitario) * 100) / 100;

function getTaxaRate(taxa_iva_id: number | null): number {
    if (!taxa_iva_id) return 0;
    return props.taxas.find((t) => t.id === taxa_iva_id)?.taxa ?? 0;
}

function getTaxaLabel(taxa_iva_id: number | null): string {
    if (!taxa_iva_id) return '—';
    const t = props.taxas.find((t) => t.id === taxa_iva_id);
    return t ? t.nome : '—';
}

const total = computed(() => form.linhas.reduce((s, l) => s + subtotal(l), 0));

const totalIva = computed(() =>
    form.linhas.reduce((s, l) => {
        const rate = getTaxaRate(l.taxa_iva_id);
        return s + Math.round(subtotal(l) * (rate / 100) * 100) / 100;
    }, 0),
);

const totalComIva = computed(() => total.value + totalIva.value);

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
            subtotal: subtotal(l),
        })),
    }));
    if (isEditing.value && props.encomenda?.id) {
        form.put(`/encomendas/clientes/${props.encomenda.id}`);
    } else {
        form.post('/encomendas/clientes');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl p-6">
            <h1 class="mb-6 text-xl font-semibold">
                {{ isEditing ? 'Editar' : 'Nova' }} Encomenda — Cliente
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
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

                    <!-- Data -->
                    <div class="space-y-2">
                        <Label for="data_encomenda">Data</Label>
                        <Input
                            id="data_encomenda"
                            v-model="form.data_encomenda"
                            type="date"
                        />
                        <InputError :message="form.errors.data_encomenda" />
                    </div>

                    <!-- Estado -->
                    <div class="space-y-2">
                        <Label for="estado">Estado</Label>
                        <Select v-model="form.estado">
                            <SelectTrigger id="estado"
                                ><SelectValue
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="em_progresso"
                                    >Em Progresso</SelectItem
                                >
                                <SelectItem value="concluida"
                                    >Concluída</SelectItem
                                >
                                <SelectItem value="cancelada"
                                    >Cancelada</SelectItem
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
                            <!-- Artigo -->
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
                                            <span class="truncate">{{
                                                linha.artigo_nome ||
                                                'Pesquisar artigo...'
                                            }}</span>
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

                            <!-- IVA -->
                            <div
                                class="col-span-6 min-w-0 space-y-1 md:col-span-3"
                            >
                                <Label>IVA</Label>
                                <div
                                    class="flex h-9 items-center rounded-md border bg-muted/40 px-3 text-sm text-muted-foreground"
                                >
                                    {{ getTaxaLabel(linha.taxa_iva_id) }}
                                </div>
                            </div>

                            <!-- Qtd -->
                            <div
                                class="col-span-3 min-w-0 space-y-1 md:col-span-2"
                            >
                                <Label>Qtd.</Label>
                                <Input
                                    v-model="linha.quantidade"
                                    type="number"
                                    min="1"
                                    step="1"
                                />
                                <InputError
                                    :message="
                                        (form.errors as any)[
                                            `linhas.${i}.quantidade`
                                        ]
                                    "
                                />
                            </div>

                            <!-- P. Unit -->
                            <div
                                class="col-span-3 min-w-0 space-y-1 md:col-span-2"
                            >
                                <Label>P. Unit.</Label>
                                <div
                                    class="flex h-9 items-center rounded-md border bg-muted/40 px-3 text-sm text-muted-foreground"
                                >
                                    {{ fmtEur(Number(linha.preco_unitario)) }}
                                </div>
                            </div>

                            <!-- Subtotal -->
                            <div
                                class="col-span-12 flex items-end justify-between"
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

                    <div
                        v-if="form.linhas.length > 0"
                        class="flex justify-end pt-2"
                    >
                        <div
                            class="space-y-1 rounded-md border px-6 py-3 text-right"
                        >
                            <div
                                class="flex justify-between gap-12 text-sm text-muted-foreground"
                            >
                                <span>Subtotal (s/ IVA)</span>
                                <span>{{ fmtEur(total) }}</span>
                            </div>
                            <div
                                class="flex justify-between gap-12 text-sm text-muted-foreground"
                            >
                                <span>IVA</span>
                                <span>{{ fmtEur(totalIva) }}</span>
                            </div>
                            <div
                                class="flex justify-between gap-12 border-t pt-1"
                            >
                                <span class="font-semibold"
                                    >Total (c/ IVA)</span
                                >
                                <span class="text-xl font-bold">{{
                                    fmtEur(totalComIva)
                                }}</span>
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
                        <a href="/encomendas/clientes">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
