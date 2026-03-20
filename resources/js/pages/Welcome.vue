<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { store as loginRoute } from '@/routes/login';

const page = usePage();
const auth = computed(
    () => page.props.auth as { user: { name: string } | null },
);
const empresa = computed(
    () =>
        page.props.empresa as {
            nome: string | null;
            logo_url: string | null;
        } | null,
);
const appName = computed(
    () => empresa.value?.nome || (page.props.name as string) || 'Gestão',
);
</script>

<template>
    <Head :title="appName" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <!-- Top nav -->
        <header
            class="sticky top-0 z-50 border-b bg-background/80 backdrop-blur-sm"
        >
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6"
            >
                <!-- Logo + name -->
                <Link href="/" class="flex items-center gap-3">
                    <img
                        v-if="empresa?.logo_url"
                        :src="empresa.logo_url"
                        :alt="appName"
                        class="h-8 w-auto object-contain"
                    />
                    <AppLogoIcon
                        v-else
                        class="size-8 fill-current text-foreground"
                    />
                    <span class="text-base font-semibold tracking-tight">{{
                        appName
                    }}</span>
                </Link>

                <!-- Auth actions -->
                <div class="flex items-center gap-3">
                    <Button v-if="auth?.user" as-child>
                        <Link :href="dashboard()">Ir para o Dashboard</Link>
                    </Button>
                    <Button v-else as-child>
                        <Link :href="loginRoute.url()">Entrar</Link>
                    </Button>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section
            class="flex flex-1 flex-col items-center justify-center px-6 py-24 text-center"
        >
            <div
                class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl border bg-muted shadow-sm"
            >
                <img
                    v-if="empresa?.logo_url"
                    :src="empresa.logo_url"
                    :alt="appName"
                    class="h-10 w-auto object-contain"
                />
                <AppLogoIcon
                    v-else
                    class="size-10 fill-current text-foreground"
                />
            </div>

            <h1
                class="mb-4 max-w-2xl text-4xl font-bold tracking-tight sm:text-5xl"
            >
                {{ appName }}
            </h1>
            <p class="mb-8 max-w-xl text-lg text-muted-foreground">
                Plataforma integrada de gestão comercial.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <Button v-if="auth?.user" size="lg" as-child>
                    <Link :href="dashboard()">Ir para o Dashboard</Link>
                </Button>
                <Button v-else size="lg" as-child>
                    <Link :href="loginRoute.url()">Entrar na plataforma</Link>
                </Button>
            </div>
        </section>

        <!-- Footer -->
        <footer
            class="border-t px-6 py-6 text-center text-sm text-muted-foreground"
        >
            © {{ new Date().getFullYear() }} {{ appName }}. Todos os direitos
            reservados.
        </footer>
    </div>
</template>
