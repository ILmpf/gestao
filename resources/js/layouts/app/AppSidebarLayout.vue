<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, X } from 'lucide-vue-next';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<{ flash?: { success?: string; error?: string } }>();
const flashMessage = ref<{ type: 'success' | 'error'; message: string } | null>(
    null,
);
let dismissTimer: ReturnType<typeof setTimeout> | undefined;

watch(
    () => [page.props.flash?.success, page.props.flash?.error] as const,
    ([success, error]) => {
        clearTimeout(dismissTimer);
        if (success) {
            flashMessage.value = { type: 'success', message: success };
            dismissTimer = setTimeout(() => (flashMessage.value = null), 4000);
        } else if (error) {
            flashMessage.value = { type: 'error', message: error };
            dismissTimer = setTimeout(() => (flashMessage.value = null), 6000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />

            <!-- Flash notification -->
            <Transition
                enter-active-class="transition-all duration-300"
                enter-from-class="opacity-0 translate-y-[-8px]"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-[-8px]"
            >
                <div
                    v-if="flashMessage"
                    class="fixed top-4 right-4 z-50 flex max-w-sm items-start gap-3 rounded-lg border px-4 py-3 shadow-lg"
                    :class="
                        flashMessage.type === 'success'
                            ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200'
                            : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200'
                    "
                >
                    <CheckCircle
                        v-if="flashMessage.type === 'success'"
                        class="mt-0.5 h-4 w-4 shrink-0"
                    />
                    <AlertCircle v-else class="mt-0.5 h-4 w-4 shrink-0" />
                    <span class="flex-1 text-sm">{{
                        flashMessage.message
                    }}</span>
                    <button
                        class="ml-1 shrink-0 opacity-60 hover:opacity-100"
                        @click="flashMessage = null"
                    >
                        <X class="h-3 w-3" />
                    </button>
                </div>
            </Transition>

            <slot />
        </AppContent>
    </AppShell>
</template>
