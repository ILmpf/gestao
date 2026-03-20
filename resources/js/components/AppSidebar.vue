<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Activity,
    Archive,
    BarChart3,
    BookOpen,
    Briefcase,
    Building,
    Calendar,
    ClipboardList,
    CreditCard,
    FileText,
    Globe,
    LayoutGrid,
    Lock,
    Package,
    Percent,
    Receipt,
    Settings,
    Shield,
    ShoppingCart,
    Tag,
    Truck,
    User,
    UserRound,
    Users,
    Zap,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainItems: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
];

const comercialItems: NavItem[] = [
    { title: 'Entidades', href: '/entidades', icon: BookOpen },
    { title: 'Clientes', href: '/clientes', icon: Users },
    { title: 'Fornecedores', href: '/fornecedores', icon: Truck },
    { title: 'Contatos', href: '/contactos', icon: UserRound },
    { title: 'Propostas', href: '/propostas', icon: FileText },
    { title: 'Calendário', href: '/calendario', icon: Calendar },
];

const operacionalItems: NavItem[] = [
    {
        title: 'Encomendas',
        href: '/encomendas',
        icon: ShoppingCart,
        children: [
            { title: 'Clientes', href: '/encomendas/clientes', icon: Users },
            {
                title: 'Fornecedores',
                href: '/encomendas/fornecedores',
                icon: Truck,
            },
        ],
    },
    {
        title: 'Ordens de Trabalho',
        href: '/ordens-trabalho',
        icon: ClipboardList,
    },
];

const financeiroItems: NavItem[] = [
    {
        title: 'Contas Bancárias',
        href: '/financeiro/contas-bancarias',
        icon: CreditCard,
    },
    {
        title: 'Clientes',
        href: '/financeiro/clientes',
        icon: Users,
        children: [
            {
                title: 'Conta Corrente',
                href: '/financeiro/conta-corrente-clientes',
                icon: Receipt,
            },
        ],
    },
    {
        title: 'Fornecedores',
        href: '/financeiro/fornecedores',
        icon: Truck,
        children: [
            {
                title: 'Faturas',
                href: '/financeiro/faturas-fornecedores',
                icon: FileText,
            },
            {
                title: 'Notas de Crédito',
                href: '/financeiro/notas-credito-fornecedores',
                icon: FileText,
            },
        ],
    },
    { title: 'Arquivo Digital', href: '/arquivo-digital', icon: Archive },
];

const administracaoItems: NavItem[] = [
    {
        title: 'Gestão de Acessos',
        href: '/gestao-acessos',
        icon: Shield,
        children: [
            {
                title: 'Utilizadores',
                href: '/gestao-acessos/utilizadores',
                icon: User,
            },
            {
                title: 'Permissões',
                href: '/gestao-acessos/permissoes',
                icon: Lock,
            },
        ],
    },
    {
        title: 'Configurações',
        href: '/configuracoes',
        icon: Settings,
        children: [
            {
                title: 'Entidades - Países',
                href: '/configuracoes/paises',
                icon: Globe,
            },
            {
                title: 'Contatos - Funções',
                href: '/configuracoes/funcoes-contacto',
                icon: Briefcase,
            },
            {
                title: 'Calendário - Tipos',
                href: '/configuracoes/tipos-calendario',
                icon: Tag,
            },
            {
                title: 'Calendário - Acções',
                href: '/configuracoes/accoes-calendario',
                icon: Zap,
            },
            { title: 'Artigos', href: '/configuracoes/artigos', icon: Package },
            {
                title: 'Financeiro - IVA',
                href: '/configuracoes/taxas-iva',
                icon: Percent,
            },
            { title: 'Logs', href: '/configuracoes/logs', icon: Activity },
            {
                title: 'Empresa',
                href: '/configuracoes/empresa',
                icon: Building,
            },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainItems" />
            <SidebarSeparator />
            <NavMain :items="comercialItems" label="Comercial" />
            <SidebarSeparator />
            <NavMain :items="operacionalItems" label="Operacional" />
            <SidebarSeparator />
            <NavMain :items="financeiroItems" label="Financeiro" />
            <SidebarSeparator />
            <NavMain :items="administracaoItems" label="Administração" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
