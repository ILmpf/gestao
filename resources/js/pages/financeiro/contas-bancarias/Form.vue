<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ContaData = {
    id?: number;
    nome?: string;
    iban?: string | null;
    bic?: string | null;
    ativa?: boolean;
};

const props = defineProps<{ conta?: ContaData }>();

const isEditing = computed(() => Boolean(props.conta?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Financeiro', href: '/financeiro' },
    { title: 'Contas Bancárias', href: '/financeiro/contas-bancarias' },
    { title: isEditing.value ? 'Editar' : 'Nova', href: '#' },
];

const form = useForm({
    nome: props.conta?.nome ?? '',
    iban: props.conta?.iban ?? '',
    bic: props.conta?.bic ?? '',
    ativa: props.conta?.ativa ?? true,
});

function submit(): void {
    if (isEditing.value) {
        form.put(`/financeiro/contas-bancarias/${props.conta!.id}`);
    } else {
        form.post('/financeiro/contas-bancarias');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-xl space-y-6 p-6">
            <h1 class="text-xl font-semibold">
                {{
                    isEditing ? 'Editar Conta Bancária' : 'Nova Conta Bancária'
                }}
            </h1>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-1">
                    <Label for="nome">Nome *</Label>
                    <Input
                        id="nome"
                        v-model="form.nome"
                        placeholder="Ex: Conta Caixa"
                    />
                    <InputError :message="form.errors.nome" />
                </div>

                <div class="space-y-1">
                    <Label for="iban">IBAN</Label>
                    <Input
                        id="iban"
                        v-model="form.iban"
                        placeholder="PT50XXXX..."
                    />
                    <InputError :message="form.errors.iban" />
                </div>

                <div class="space-y-1">
                    <Label for="bic">BIC / SWIFT</Label>
                    <Input id="bic" v-model="form.bic" placeholder="XXXXXXXX" />
                    <InputError :message="form.errors.bic" />
                </div>

                <div class="flex items-center gap-3">
                    <Switch id="ativa" v-model:checked="form.ativa" />
                    <Label for="ativa">Conta ativa</Label>
                    <InputError :message="form.errors.ativa" />
                </div>

                <div class="flex gap-3 pt-2">
                    <Button type="submit" :disabled="form.processing">
                        {{ isEditing ? 'Guardar' : 'Criar' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <a href="/financeiro/contas-bancarias">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
