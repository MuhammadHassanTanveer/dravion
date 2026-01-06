<template>
    <div class="space-y-4">
        <!-- Search and Filters -->
        <div v-if="showGlobalSearch !== false" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-0">
            <div class="flex items-center space-x-2 flex-1">
                <div class="relative w-full sm:w-auto">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="pl-9 sm:pl-10 w-full sm:w-64"
                    />
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <select v-model="pageSize" class="h-9 sm:h-10 px-2 sm:px-3 rounded-md border border-gray-300 text-xs sm:text-sm w-full sm:w-auto">
                    <option :value="10">10 per page</option>
                    <option :value="25">25 per page</option>
                    <option :value="50">50 per page</option>
                    <option :value="100">100 per page</option>
                </select>
            </div>
        </div>
        <div v-else class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 sm:gap-0">
            <div class="flex items-center space-x-2">
                <select v-model="pageSize" class="h-9 sm:h-10 px-2 sm:px-3 rounded-md border border-gray-300 text-xs sm:text-sm w-full sm:w-auto">
                    <option :value="10">10 per page</option>
                    <option :value="25">25 per page</option>
                    <option :value="50">50 per page</option>
                    <option :value="100">100 per page</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden overflow-x-auto">
            <Table class="min-w-full">
                <TableHeader>
                    <TableRow>
                        <TableHead 
                            v-for="column in columns" 
                            :key="column.key"
                            :class="[
                                column.sortable ? 'cursor-pointer hover:bg-gray-50' : '',
                                column.key === 'actions' ? 'text-center' : ''
                            ]"
                            @click="column.sortable && sortBy(column.key)"
                        >
                            <div class="flex items-center space-x-2">
                                <span>{{ column.label }}</span>
                                <span v-if="column.sortable" class="flex flex-col">
                                    <svg 
                                        class="w-3 h-3" 
                                        :class="sortColumn === column.key && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-400'"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                    <svg 
                                        class="w-3 h-3 -mt-1" 
                                        :class="sortColumn === column.key && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-400'"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </div>
                        </TableHead>
                    </TableRow>
                    <!-- Filter Row -->
                    <TableRow v-if="columnFilters">
                        <TableHead 
                            v-for="column in columns" 
                            :key="`filter-${column.key}`"
                            :class="column.key === 'actions' ? 'text-center' : ''"
                            class="p-2"
                        >
                            <slot :name="`filter-${column.key}`" :column="column">
                                <div v-if="column.filterable !== false && column.key !== 'actions'" class="relative">
                                    <Input
                                        v-model="columnFilters[column.key]"
                                        :placeholder="`Filter ${column.label}`"
                                        class="h-8 text-xs pr-8"
                                        @input="currentPage = 1"
                                    />
                                    <button
                                        v-if="columnFilters[column.key] && columnFilters[column.key].trim() !== ''"
                                        @click="clearColumnFilter(column.key)"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                        type="button"
                                        title="Clear filter"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </slot>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in paginatedData" :key="row.id">
                        <TableCell 
                            v-for="column in columns" 
                            :key="column.key"
                            :class="column.key === 'actions' ? 'text-right' : ''"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                {{ formatCellValue(row[column.key], column) }}
                            </slot>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="paginatedData.length === 0">
                        <TableCell :colspan="columns.length" class="text-center py-8 text-gray-500">
                            No data found
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0">
            <div class="text-xs sm:text-sm text-gray-600 text-center sm:text-left">
                Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredData.length }} entries
            </div>
            <div class="flex items-center space-x-2">
                <button
                    @click="currentPage--"
                    :disabled="currentPage === 1"
                    class="px-2 sm:px-3 py-1 rounded-md border border-gray-300 text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Previous
                </button>
                <span class="px-2 sm:px-3 py-1 text-xs sm:text-sm text-gray-700">
                    Page {{ currentPage }} of {{ totalPages }}
                </span>
                <button
                    @click="currentPage++"
                    :disabled="currentPage === totalPages"
                    class="px-2 sm:px-3 py-1 rounded-md border border-gray-300 text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Table from './Table.vue';
import TableHeader from './TableHeader.vue';
import TableBody from './TableBody.vue';
import TableRow from './TableRow.vue';
import TableHead from './TableHead.vue';
import TableCell from './TableCell.vue';
import Input from './Input.vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    columnFilters: {
        type: Object,
        default: null,
    },
    showGlobalSearch: {
        type: Boolean,
        default: true,
    },
});

const searchQuery = ref('');
const sortColumn = ref(null);
const sortDirection = ref('asc');
const currentPage = ref(1);
const pageSize = ref(10);

const filteredData = computed(() => {
    let result = [...props.data];

    // Apply column filters if provided
    if (props.columnFilters) {
        Object.keys(props.columnFilters).forEach(columnKey => {
            const filterValue = props.columnFilters[columnKey];
            if (filterValue && filterValue.trim() !== '') {
                const query = filterValue.toLowerCase();
                result = result.filter(row => {
                    // Handle nested properties (e.g., customer.customer_id)
                    const value = getNestedValue(row, columnKey);
                    return value && String(value).toLowerCase().includes(query);
                });
            }
        });
    }

    // Apply search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(row => {
            return props.columns.some(column => {
                const value = getNestedValue(row, column.key);
                return value && String(value).toLowerCase().includes(query);
            });
        });
    }

    // Apply sorting
    if (sortColumn.value) {
        result.sort((a, b) => {
            const aVal = getNestedValue(a, sortColumn.value);
            const bVal = getNestedValue(b, sortColumn.value);
            
            if (aVal === null || aVal === undefined) return 1;
            if (bVal === null || bVal === undefined) return -1;
            
            if (typeof aVal === 'string') {
                return sortDirection.value === 'asc' 
                    ? aVal.localeCompare(bVal)
                    : bVal.localeCompare(aVal);
            }
            
            return sortDirection.value === 'asc' 
                ? aVal - bVal
                : bVal - aVal;
        });
    }

    return result;
});

// Helper function to get nested property values
const getNestedValue = (obj, path) => {
    if (!path) return null;
    const keys = path.split('.');
    let value = obj;
    for (const key of keys) {
        if (value === null || value === undefined) return null;
        value = value[key];
    }
    return value;
};

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / pageSize.value);
});

const startIndex = computed(() => {
    return (currentPage.value - 1) * pageSize.value;
});

const endIndex = computed(() => {
    return Math.min(startIndex.value + pageSize.value, filteredData.value.length);
});

const paginatedData = computed(() => {
    return filteredData.value.slice(startIndex.value, endIndex.value);
});

const sortBy = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
    currentPage.value = 1;
};

const formatCellValue = (value, column) => {
    if (value === null || value === undefined) return '-';
    if (column.format && typeof column.format === 'function') {
        return column.format(value);
    }
    return value;
};

watch(searchQuery, () => {
    currentPage.value = 1;
});

watch(pageSize, () => {
    currentPage.value = 1;
});

const emit = defineEmits(['clear-filter']);

const clearColumnFilter = (columnKey) => {
    emit('clear-filter', columnKey);
    currentPage.value = 1;
};
</script>

