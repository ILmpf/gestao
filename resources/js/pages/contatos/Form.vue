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
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EntidadeOption = { id: number; nome: string };
type FuncaoOption = { id: number; nome: string };

type ContatoData = {
    id?: number;
    entidade_id?: number | null;
    primeiro_nome?: string;
    apelido?: string;
    funcao_contacto_id?: number | null;
    telefone?: string | null;
    telemovel?: string | null;
    email?: string | null;
    notas?: string | null;
    estado?: string;
};

const props = defineProps<{
    contato?: ContatoData;
    entidades: EntidadeOption[];
    funcoes: FuncaoOption[];
}>();

const isEditing = computed(() => Boolean(props.contato?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Contatos', href: '/contactos' },
    { title: isEditing.value ? 'Editar' : 'Novo', href: '#' },
];

const form = useForm({
    entidade_id: (props.contato?.entidade_id ?? null) as number | null,
    primeiro_nome: props.contato?.primeiro_nome ?? '',
    apelido: props.contato?.apelido ?? '',
    funcao_contacto_id: (props.contato?.funcao_contacto_id ?? null) as
        | number
        | null,
    telefone: props.contato?.telefone ?? '',
    telemovel: props.contato?.telemovel ?? '',
    email: props.contato?.email ?? '',
    notas: props.contato?.notas ?? '',
    estado: props.contato?.estado ?? 'ativo',
});

function submit(): void {
    if (isEditing.value && props.contato?.id) {
        form.put(`/contactos/${props.contato.id}`);
    } else {
        form.post('/contactos');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl p-6">
            <h1 class="mb-6 text-xl font-semibold">
                {{ isEditing ? 'Editar' : 'Novo' }} Contato
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
                <!-- Entidade -->
                <div class="space-y-2">
                    <Label for="entidade_id"
                        >Entidade <span class="text-destructive">*</span></Label
                    >
                    <Select
                        :model-value="
                            form.entidade_id ? String(form.entidade_id) : ''
                        "
                        @update:model-value="
                            (v) => (form.entidade_id = v ? Number(v) : null)
                        "
                    >
                        <SelectTrigger id="entidade_id">
                            <SelectValue placeholder="Selecione a entidade" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="e in entidades"
                                :key="e.id"
                                :value="String(e.id)"
                            >
                                {{ e.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.entidade_id" />
                </div>

                <!-- Nome + Apelido -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="primeiro_nome"
                            >Nome <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="primeiro_nome"
                            v-model="form.primeiro_nome"
                            placeholder="Nome"
                        />
                        <InputError :message="form.errors.primeiro_nome" />
                    </div>
                    <div class="space-y-2">
                        <Label for="apelido"
                            >Apelido
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="apelido"
                            v-model="form.apelido"
                            placeholder="Apelido"
                        />
                        <InputError :message="form.errors.apelido" />
                    </div>
                </div>

                <!-- Função -->
                <div class="space-y-2">
                    <Label for="funcao_contacto_id">Função</Label>
                    <Select
                        :model-value="
                            form.funcao_contacto_id
                                ? String(form.funcao_contacto_id)
                                : ''
                        "
                        @update:model-value="
                            (v) =>
                                (form.funcao_contacto_id = v ? Number(v) : null)
                        "
                    >
                        <SelectTrigger id="funcao_contacto_id">
                            <SelectValue placeholder="Selecione a função" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="f in funcoes"
                                :key="f.id"
                                :value="String(f.id)"
                            >
                                {{ f.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.funcao_contacto_id" />
                </div>

                <!-- Telefone + Telemóvel -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="telefone">Telefone</Label>
                        <Input
                            id="telefone"
                            v-model="form.telefone"
                            placeholder="210 000 000"
                        />
                        <InputError :message="form.errors.telefone" />
                    </div>
                    <div class="space-y-2">
                        <Label for="telemovel">Telemóvel</Label>
                        <Input
                            id="telemovel"
                            v-model="form.telemovel"
                            placeholder="910 000 000"
                        />
                        <InputError :message="form.errors.telemovel" />
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="email@empresa.pt"
                    />
                    <InputError :message="form.errors.email" />
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

                <!-- Ações -->
                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing">
                        {{ isEditing ? 'Guardar' : 'Criar' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <a href="/contactos">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
