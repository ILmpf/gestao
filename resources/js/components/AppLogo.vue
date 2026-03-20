<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage<{
    empresa: { nome: string | null; logo_url: string | null };
}>();
const empresa = page.props.empresa;
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <img
            v-if="empresa?.logo_url"
            :src="empresa.logo_url"
            alt="Logo"
            class="size-8 object-contain"
        />
        <AppLogoIcon
            v-else
            class="size-5 fill-current text-white dark:text-black"
        />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            {{ empresa?.nome || 'Laravel Starter Kit' }}
        </span>
    </div>
</template>
