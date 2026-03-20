<script setup lang="ts">
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
    type ColumnDef,
    type SortingState,
} from '@tanstack/vue-table';
import {
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    ChevronUp,
    ChevronsUpDown,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps<{
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    data: any[];
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    columns: ColumnDef<any>[];
    searchPlaceholder?: string;
    actionsLabel?: string;
}>();

defineSlots<{
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    actions(props: { row: any }): any;
}>();

const sorting = ref<SortingState>([]);
const globalFilter = ref('');

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    state: {
        get sorting() {
            return sorting.value;
        },
        get globalFilter() {
            return globalFilter.value;
        },
    },
    onSortingChange: (updater) => {
        sorting.value =
            typeof updater === 'function' ? updater(sorting.value) : updater;
    },
    onGlobalFilterChange: (updater) => {
        globalFilter.value =
            typeof updater === 'function'
                ? updater(globalFilter.value)
                : updater;
    },
    initialState: {
        pagination: { pageSize: 15 },
    },
});
</script>

<template>
    <div class="space-y-4">
        <!-- Search -->
        <Input
            v-model="globalFilter"
            :placeholder="searchPlaceholder ?? 'Pesquisar...'"
            class="max-w-sm"
        />

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            :class="
                                header.column.getCanSort()
                                    ? 'cursor-pointer select-none'
                                    : ''
                            "
                            @click="
                                header.column.getToggleSortingHandler()?.(
                                    $event,
                                )
                            "
                        >
                            <div class="flex items-center gap-1">
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                                <template v-if="header.column.getCanSort()">
                                    <ChevronUp
                                        v-if="
                                            header.column.getIsSorted() ===
                                            'asc'
                                        "
                                        class="h-4 w-4"
                                    />
                                    <ChevronDown
                                        v-else-if="
                                            header.column.getIsSorted() ===
                                            'desc'
                                        "
                                        class="h-4 w-4"
                                    />
                                    <ChevronsUpDown
                                        v-else
                                        class="h-4 w-4 opacity-40"
                                    />
                                </template>
                            </div>
                        </TableHead>
                        <TableHead v-if="$slots.actions" class="text-right">
                            {{ actionsLabel ?? 'Ações' }}
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                            <TableCell v-if="$slots.actions" class="text-right">
                                <slot name="actions" :row="row.original" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell
                                :colspan="
                                    columns.length + ($slots.actions ? 1 : 0)
                                "
                                class="h-24 text-center text-muted-foreground"
                            >
                                Sem resultados.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <span>Linhas por página</span>
                <Select
                    :model-value="String(table.getState().pagination.pageSize)"
                    @update:model-value="(v) => table.setPageSize(Number(v))"
                >
                    <SelectTrigger class="h-8 w-17.5">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="size in [10, 15, 25, 50]"
                            :key="size"
                            :value="String(size)"
                        >
                            {{ size }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-muted-foreground">
                    Página {{ table.getState().pagination.pageIndex + 1 }} de
                    {{ table.getPageCount() }}
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="!table.getCanPreviousPage()"
                        @click="table.previousPage()"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="!table.getCanNextPage()"
                        @click="table.nextPage()"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
