<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{
    empresa: {
        id: number;
        nome: string | null;
        logo: string | null;
        logo_url: string | null;
        morada: string | null;
        codigo_postal: string | null;
        cidade: string | null;
        nif: string | null;
    };
}>();

const form = useForm({
    nome: props.empresa.nome ?? '',
    morada: props.empresa.morada ?? '',
    codigo_postal: props.empresa.codigo_postal ?? '',
    cidade: props.empresa.cidade ?? '',
    nif: props.empresa.nif ?? '',
    logo: null as File | null,
});

function submit() {
    form.post('/configuracoes/empresa?_method=PUT', {
        preserveScroll: true,
        forceFormData: true,
    });
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Configurações', href: '/configuracoes' },
            { title: 'Empresa', href: '/configuracoes/empresa' },
        ]"
    >
        <div class="max-w-2xl p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Dados da Empresa</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="space-y-4" @submit.prevent="submit">
                        <!-- Logo -->
                        <div class="space-y-2">
                            <Label>Logotipo</Label>
                            <div v-if="empresa.logo_url" class="mb-2">
                                <img
                                    :src="empresa.logo_url"
                                    alt="Logo"
                                    class="h-16 rounded border object-contain p-1"
                                />
                            </div>
                            <Input
                                type="file"
                                accept="image/*"
                                @change="
                                    (e: any) => (form.logo = e.target.files[0])
                                "
                            />
                            <p
                                v-if="form.errors.logo"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.logo }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label>Nome</Label>
                            <Input
                                v-model="form.nome"
                                placeholder="Nome da empresa"
                            />
                            <p
                                v-if="form.errors.nome"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.nome }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label>NIF</Label>
                            <Input
                                v-model="form.nif"
                                placeholder="Número de Contribuinte"
                            />
                            <p
                                v-if="form.errors.nif"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.nif }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label>Morada</Label>
                            <Input v-model="form.morada" placeholder="Morada" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>Código Postal</Label>
                                <Input
                                    v-model="form.codigo_postal"
                                    placeholder="0000-000"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>Localidade</Label>
                                <Input
                                    v-model="form.cidade"
                                    placeholder="Cidade"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <Button type="submit" :disabled="form.processing"
                                >Guardar</Button
                            >
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
