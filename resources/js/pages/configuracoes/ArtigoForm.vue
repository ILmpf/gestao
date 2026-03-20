<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Loader2, X } from 'lucide-vue-next';
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
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type TaxaOption = { id: number; nome: string; taxa: number };

type ArtigoData = {
    id?: number;
    referencia?: string;
    nome?: string;
    descricao?: string | null;
    preco?: number | string;
    taxa_iva_id?: number | null;
    imagem_url?: string | null;
    imagem_artigo?: string | null;
    notas?: string | null;
    estado?: string;
};

const props = defineProps<{
    artigo?: ArtigoData;
    taxas: TaxaOption[];
}>();

const isEditing = computed(() => Boolean(props.artigo?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Configurações', href: '/configuracoes' },
    { title: 'Artigos', href: '/configuracoes/artigos' },
    { title: isEditing.value ? 'Editar' : 'Novo', href: '#' },
];

const form = useForm({
    referencia: props.artigo?.referencia ?? '',
    nome: props.artigo?.nome ?? '',
    descricao: props.artigo?.descricao ?? '',
    preco: props.artigo?.preco ?? '',
    taxa_iva_id: (props.artigo?.taxa_iva_id ?? null) as number | null,
    imagem_artigo: null as File | null,
    notas: props.artigo?.notas ?? '',
    estado: props.artigo?.estado ?? 'ativo',
});

// Local image preview
const previewUrl = ref<string | null>(props.artigo?.imagem_url ?? null);

function onFileChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    form.imagem_artigo = file;
    if (file) {
        previewUrl.value = URL.createObjectURL(file);
    }
}

function clearImage(): void {
    form.imagem_artigo = null;
    previewUrl.value = props.artigo?.imagem_url ?? null;
}

function submit(): void {
    if (isEditing.value && props.artigo?.id) {
        form.put(`/configuracoes/artigos/${props.artigo.id}`);
    } else {
        form.post('/configuracoes/artigos');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl p-6">
            <h1 class="mb-6 text-xl font-semibold">
                {{ isEditing ? 'Editar' : 'Novo' }} Artigo
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
                <!-- Referência + Nome -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="referencia"
                            >Referência
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="referencia"
                            v-model="form.referencia"
                            placeholder="ex: ART-001"
                        />
                        <InputError :message="form.errors.referencia" />
                    </div>
                    <div class="space-y-2">
                        <Label for="nome"
                            >Nome <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="nome"
                            v-model="form.nome"
                            placeholder="Nome do artigo"
                        />
                        <InputError :message="form.errors.nome" />
                    </div>
                </div>

                <!-- Descrição -->
                <div class="space-y-2">
                    <Label for="descricao">Descrição</Label>
                    <Textarea
                        id="descricao"
                        v-model="form.descricao"
                        :rows="3"
                        placeholder="Descrição do artigo..."
                    />
                    <InputError :message="form.errors.descricao" />
                </div>

                <!-- Preço + IVA -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="preco"
                            >Preço (€)
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="preco"
                            v-model="form.preco"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                        />
                        <InputError :message="form.errors.preco" />
                    </div>
                    <div class="space-y-2">
                        <Label for="taxa_iva_id">IVA</Label>
                        <Select
                            :model-value="
                                form.taxa_iva_id ? String(form.taxa_iva_id) : ''
                            "
                            @update:model-value="
                                (v: string) =>
                                    (form.taxa_iva_id = v ? Number(v) : null)
                            "
                        >
                            <SelectTrigger id="taxa_iva_id">
                                <SelectValue placeholder="Selecione o IVA" />
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
                        <InputError :message="form.errors.taxa_iva_id" />
                    </div>
                </div>

                <!-- Foto -->
                <div class="space-y-2">
                    <Label for="imagem_artigo">Foto</Label>
                    <div class="flex items-start gap-4">
                        <div v-if="previewUrl" class="relative">
                            <img
                                :src="previewUrl"
                                alt="Pré-visualização"
                                class="h-24 w-24 rounded border object-cover"
                            />
                            <button
                                type="button"
                                class="absolute -top-2 -right-2 rounded-full bg-destructive p-0.5 text-white"
                                title="Remover pré-visualização"
                                @click="clearImage"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </div>
                        <div class="flex-1 space-y-1">
                            <Input
                                id="imagem_artigo"
                                type="file"
                                accept="image/*"
                                class="cursor-pointer"
                                @change="onFileChange"
                            />
                            <p class="text-xs text-muted-foreground">
                                JPG, PNG, WEBP — máx. 4 MB. Ficheiro armazenado
                                de forma privada.
                            </p>
                        </div>
                    </div>
                    <InputError :message="form.errors.imagem_artigo" />
                </div>

                <!-- Observações -->
                <div class="space-y-2">
                    <Label for="notas">Observações</Label>
                    <Textarea
                        id="notas"
                        v-model="form.notas"
                        :rows="3"
                        placeholder="Observações..."
                    />
                    <InputError :message="form.errors.notas" />
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
                        <a href="/configuracoes/artigos">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
