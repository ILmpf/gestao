<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const props = defineProps<{
    items: NavItem[];
    label?: string;
}>();

const page = usePage();

const isActive = (href: string) =>
    page.url === href || page.url.startsWith(href + '/');

const isGroupActive = (item: NavItem): boolean => {
    if (isActive(item.href)) return true;
    return item.children?.some((c) => isActive(c.href)) ?? false;
};

const STORAGE_KEY = 'sidebar_open_groups';

function loadStored(): Record<string, boolean> {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '{}');
    } catch {
        return {};
    }
}

const stored = loadStored();

// Compute initial states: use stored value if present, otherwise derive from active route
const initialStates = Object.fromEntries(
    props.items
        .filter((item) => item.children?.length)
        .map((item) => [
            item.title,
            item.title in stored ? stored[item.title] : isGroupActive(item),
        ]),
);

// Persist on every mount so active-route auto-opens survive navigation
try {
    localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({ ...stored, ...initialStates }),
    );
} catch {
    // ignore
}

const openStates = ref<Record<string, boolean>>(initialStates);

function setGroupOpen(title: string, value: boolean): void {
    openStates.value[title] = value;
    try {
        // Merge with current stored value to avoid overwriting other NavMain instances
        localStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({ ...loadStored(), [title]: value }),
        );
    } catch {
        // ignore storage errors
    }
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel v-if="label">{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Item with children → collapsible -->
                <Collapsible
                    v-if="item.children?.length"
                    as-child
                    :open="openStates[item.title]"
                    class="group/collapsible"
                    @update:open="(v) => setGroupOpen(item.title, v)"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton
                                :tooltip="item.title"
                                :is-active="isGroupActive(item)"
                            >
                                <component :is="item.icon" v-if="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem
                                    v-for="child in item.children"
                                    :key="child.title"
                                >
                                    <SidebarMenuSubButton
                                        as-child
                                        :is-active="isActive(child.href)"
                                    >
                                        <Link :href="child.href">
                                            <component
                                                :is="child.icon"
                                                v-if="child.icon"
                                            />
                                            <span>{{ child.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <!-- Flat item -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton
                        as-child
                        :is-active="isActive(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
