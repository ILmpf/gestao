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

type RoleOption = { id: number; name: string };

type UtilizadorData = {
    id?: number;
    name?: string;
    email?: string;
    telemovel?: string | null;
    role?: string | null;
    estado?: string;
};

const props = defineProps<{
    utilizador?: UtilizadorData;
    roles: RoleOption[];
}>();

const isEditing = computed(() => Boolean(props.utilizador?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestão de Acessos', href: '/gestao-acessos/utilizadores' },
    { title: 'Utilizadores', href: '/gestao-acessos/utilizadores' },
    { title: isEditing.value ? 'Editar' : 'Novo', href: '#' },
];

const form = useForm({
    name: props.utilizador?.name ?? '',
    email: props.utilizador?.email ?? '',
    telemovel: props.utilizador?.telemovel ?? '',
    password: '',
    password_confirmation: '',
    role: props.utilizador?.role ?? '',
    estado: props.utilizador?.estado ?? 'ativo',
});

function submit(): void {
    if (isEditing.value && props.utilizador?.id) {
        form.put(`/gestao-acessos/utilizadores/${props.utilizador.id}`);
    } else {
        form.post('/gestao-acessos/utilizadores');
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl p-6">
            <h1 class="mb-6 text-xl font-semibold">
                {{ isEditing ? 'Editar' : 'Novo' }} Utilizador
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
                <!-- Nome -->
                <div class="space-y-2">
                    <Label for="name">
                        Nome <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Nome completo"
                        autocomplete="name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <Label for="email">
                        Email <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="email@empresa.pt"
                        autocomplete="email"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Telemóvel -->
                <div class="space-y-2">
                    <Label for="telemovel">Telemóvel</Label>
                    <Input
                        id="telemovel"
                        v-model="form.telemovel"
                        placeholder="910 000 000"
                    />
                    <InputError :message="form.errors.telemovel" />
                </div>

                <!-- Palavra-passe -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="password">
                            Palavra-passe
                            <span v-if="!isEditing" class="text-destructive"
                                >*</span
                            >
                            <span v-else class="text-sm text-muted-foreground">
                                (deixar em branco para manter)</span
                            >
                        </Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="space-y-2">
                        <Label for="password_confirmation"
                            >Confirmar Palavra-passe</Label
                        >
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <!-- Grupo de Permissões -->
                <div class="space-y-2">
                    <Label for="role">Grupo de Permissões</Label>
                    <Select
                        :model-value="form.role"
                        @update:model-value="(v) => (form.role = v ?? '')"
                    >
                        <SelectTrigger id="role">
                            <SelectValue placeholder="Selecione o grupo" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="r in roles"
                                :key="r.id"
                                :value="r.name"
                            >
                                {{ r.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.role" />
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
                        <a href="/gestao-acessos/utilizadores">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
