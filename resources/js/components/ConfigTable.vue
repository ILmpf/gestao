<script setup lang="ts">
import { ref, shallowRef } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps<{
    title: string;
    items: Record<string, any>[];
    columns: { key: string; label: string }[];
    storeRoute: string;
    updateRoute: (id: number) => string;
    destroyRoute: (id: number) => string;
    fields: {
        key: string;
        label: string;
        type?: 'text' | 'number' | 'toggle' | 'color';
        placeholder?: string;
    }[];
}>();

const open = ref(false);
const editing = ref<Record<string, any> | null>(null);

// shallowRef so we can fully reinitialize the form on each open,
// ensuring Inertia tracks all field keys in its data() method.
const form = shallowRef(useForm<Record<string, any>>({}));

function buildInitial(source?: Record<string, any>): Record<string, any> {
    const initial: Record<string, any> = {};
    props.fields.forEach((f) => {
        initial[f.key] = source
            ? source[f.key]
            : f.type === 'toggle'
              ? true
              : '';
    });
    return initial;
}

function openCreate() {
    editing.value = null;
    form.value = useForm(buildInitial());
    open.value = true;
}

function openEdit(item: Record<string, any>) {
    editing.value = item;
    form.value = useForm(buildInitial(item));
    open.value = true;
}

function submit() {
    if (editing.value) {
        form.value.put(props.updateRoute(editing.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
            },
        });
    } else {
        form.value.post(props.storeRoute, {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
            },
        });
    }
}

function destroy(item: Record<string, any>) {
    if (confirm(`Eliminar "${item.nome}"?`)) {
        router.delete(props.destroyRoute(item.id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ title }}</h2>
            <Button size="sm" @click="openCreate">
                <Plus class="mr-2 h-4 w-4" /> Novo
            </Button>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead v-for="col in columns" :key="col.key">{{
                            col.label
                        }}</TableHead>
                        <TableHead class="w-25">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="item in items" :key="item.id">
                        <TableCell v-for="col in columns" :key="col.key">
                            <template v-if="typeof item[col.key] === 'boolean'">
                                <span
                                    :class="
                                        item[col.key]
                                            ? 'text-green-600'
                                            : 'text-red-500'
                                    "
                                >
                                    {{ item[col.key] ? 'Ativo' : 'Inativo' }}
                                </span>
                            </template>
                            <template
                                v-else-if="
                                    typeof item[col.key] === 'string' &&
                                    /^#[0-9a-f]{3,8}$/i.test(item[col.key])
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-block h-4 w-4 rounded-full border shadow-sm"
                                        :style="{
                                            backgroundColor: item[col.key],
                                        }"
                                    ></span>
                                    <span
                                        class="font-mono text-xs text-muted-foreground"
                                        >{{ item[col.key] }}</span
                                    >
                                </div>
                            </template>
                            <template v-else>{{ item[col.key] }}</template>
                        </TableCell>
                        <TableCell>
                            <div class="flex gap-2">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="openEdit(item)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="destroy(item)"
                                >
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="!items.length">
                        <TableCell
                            :colspan="columns.length + 1"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Sem registos.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Dialog v-model:open="open">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle
                        >{{ editing ? 'Editar' : 'Novo' }}
                        {{ title }}</DialogTitle
                    >
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submit">
                    <div v-for="field in fields" :key="field.key">
                        <div
                            v-if="field.type === 'toggle'"
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <span class="text-sm font-medium">{{
                                field.label
                            }}</span>
                            <Switch v-model="(form as any)[field.key]" />
                        </div>
                        <div
                            v-else-if="field.type === 'color'"
                            class="space-y-1"
                        >
                            <label class="text-sm font-medium">{{
                                field.label
                            }}</label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    v-model="(form as any)[field.key]"
                                    class="h-9 w-14 cursor-pointer rounded border p-1"
                                />
                                <Input
                                    v-model="(form as any)[field.key]"
                                    placeholder="#3b82f6"
                                    class="font-mono text-xs"
                                />
                            </div>
                            <p
                                v-if="form.errors[field.key]"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors[field.key] }}
                            </p>
                        </div>
                        <div v-else class="space-y-1">
                            <label class="text-sm font-medium">{{
                                field.label
                            }}</label>
                            <Input
                                v-model="(form as any)[field.key]"
                                :type="field.type ?? 'text'"
                                :placeholder="field.placeholder ?? field.label"
                            />
                            <p
                                v-if="form.errors[field.key]"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors[field.key] }}
                            </p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="open = false"
                            >Cancelar</Button
                        >
                        <Button type="submit" :disabled="form.processing"
                            >Guardar</Button
                        >
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
