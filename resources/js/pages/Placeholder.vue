<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <AppLayout>
        <div
            class="flex min-h-[400px] flex-col items-center justify-center gap-4 text-muted-foreground"
        >
            <div class="text-5xl">🚧</div>
            <h2 class="text-xl font-semibold text-foreground">
                Em desenvolvimento
            </h2>
            <p class="text-sm">Esta secção será implementada em breve.</p>
        </div>
    </AppLayout>
</template>
