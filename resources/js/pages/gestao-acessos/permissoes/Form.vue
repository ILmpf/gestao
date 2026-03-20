<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Menu = { label: string; key: string };

type PermissaoData = {
    id?: number;
    name?: string;
    permissions?: string[];
};

const props = defineProps<{
    permissao?: PermissaoData;
    menus: Menu[];
    actions: string[];
}>();

const page = usePage();
const errors = computed(
    () => (page.props.errors ?? {}) as Record<string, string>,
);

const isEditing = computed(() => Boolean(props.permissao?.id));
const processing = ref(false);
const name = ref(props.permissao?.name ?? '');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestão de Acessos', href: '/gestao-acessos/utilizadores' },
    { title: 'Permissões', href: '/gestao-acessos/permissoes' },
    { title: isEditing.value ? 'Editar' : 'Novo Grupo', href: '#' },
];

const selectedPermissions = ref<string[]>(
    props.permissao?.permissions?.slice() ?? [],
);

function permKey(menuKey: string, action: string): string {
    return `${menuKey}.${action}`;
}

function isMenuFullySelected(menuKey: string): boolean {
    return props.actions.every((a) =>
        selectedPermissions.value.includes(permKey(menuKey, a)),
    );
}

function isMenuPartiallySelected(menuKey: string): boolean {
    const count = props.actions.filter((a) =>
        selectedPermissions.value.includes(permKey(menuKey, a)),
    ).length;
    return count > 0 && count < props.actions.length;
}

function toggleAllForMenu(menuKey: string): void {
    const menuKeys = props.actions.map((a) => permKey(menuKey, a));
    if (isMenuFullySelected(menuKey)) {
        selectedPermissions.value = selectedPermissions.value.filter(
            (p) => !menuKeys.includes(p),
        );
    } else {
        const toAdd = menuKeys.filter(
            (k) => !selectedPermissions.value.includes(k),
        );
        selectedPermissions.value = [...selectedPermissions.value, ...toAdd];
    }
}

function submit(): void {
    processing.value = true;

    const data = {
        name: name.value,
        permissions: selectedPermissions.value,
    };

    const options = {
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEditing.value && props.permissao?.id) {
        router.put(
            `/gestao-acessos/permissoes/${props.permissao.id}`,
            data,
            options,
        );
    } else {
        router.post('/gestao-acessos/permissoes', data, options);
    }
}

const actionLabels: Record<string, string> = {
    ver: 'Ver',
    criar: 'Criar',
    editar: 'Editar',
    eliminar: 'Eliminar',
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl p-6">
            <h1 class="mb-6 text-xl font-semibold">
                {{ isEditing ? 'Editar' : 'Novo' }} Grupo de Permissões
            </h1>

            <form class="space-y-6" @submit.prevent="submit">
                <!-- Nome do grupo -->
                <div class="space-y-2">
                    <Label for="name">
                        Nome do Grupo <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="name"
                        v-model="name"
                        placeholder="Ex: Administrador, Comercial..."
                    />
                    <InputError :message="errors.name" />
                </div>

                <!-- Tabela de permissões -->
                <div class="space-y-2">
                    <Label>Permissões</Label>
                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Menu
                                    </th>
                                    <th
                                        v-for="action in actions"
                                        :key="action"
                                        class="px-4 py-3 text-center font-medium capitalize"
                                    >
                                        {{ actionLabels[action] ?? action }}
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center font-medium"
                                    >
                                        Todos
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="menu in menus"
                                    :key="menu.key"
                                    class="border-b last:border-0 hover:bg-muted/30"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ menu.label }}
                                    </td>
                                    <td
                                        v-for="action in actions"
                                        :key="action"
                                        class="px-4 py-3 text-center"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="permKey(menu.key, action)"
                                            v-model="selectedPermissions"
                                            class="h-4 w-4 cursor-pointer rounded border-gray-300 accent-primary"
                                        />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input
                                            type="checkbox"
                                            :checked="
                                                isMenuFullySelected(menu.key)
                                            "
                                            :indeterminate="
                                                isMenuPartiallySelected(
                                                    menu.key,
                                                )
                                            "
                                            class="h-4 w-4 cursor-pointer rounded border-gray-300 accent-primary"
                                            @change="toggleAllForMenu(menu.key)"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <InputError :message="errors.permissions" />
                </div>

                <!-- Ações -->
                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="processing">
                        {{ isEditing ? 'Guardar' : 'Criar' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <a href="/gestao-acessos/permissoes">Cancelar</a>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
