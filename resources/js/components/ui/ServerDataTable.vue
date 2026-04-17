<template>
    <div class="space-y-4">
        <!-- Page Size Selector -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 sm:gap-0">
            <div class="flex items-center space-x-2">
                <select v-model="localPageSize" @change="handlePageSizeChange" class="h-9 sm:h-10 px-2 sm:px-3 rounded-md border border-gray-300 text-xs sm:text-sm w-full sm:w-auto">
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
                            @click="column.sortable && handleSort(column.key)"
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
                                        v-model="localFilters[column.key]"
                                        :placeholder="`Filter ${column.label}`"
                                        class="h-8 text-xs pr-8"
                                        @input="debouncedFilter"
                                    />
                                    <button
                                        v-if="localFilters[column.key] && localFilters[column.key].trim() !== ''"
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
                    <!-- Loading State -->
                    <TableRow v-if="loading">
                        <TableCell :colspan="columns.length" class="text-center py-8">
                            <div class="flex items-center justify-center">
                                <svg class="animate-spin h-6 w-6 text-blue-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-500">Loading...</span>
                            </div>
                        </TableCell>
                    </TableRow>
                    <!-- Data Rows -->
                    <template v-else>
                        <TableRow v-for="row in data" :key="row.id">
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
                        <TableRow v-if="data.length === 0">
                            <TableCell :colspan="columns.length" class="text-center py-8 text-gray-500">
                                No data found
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0">
            <div class="text-xs sm:text-sm text-gray-600 text-center sm:text-left">
                Showing {{ startIndex }} to {{ endIndex }} of {{ total }} entries
            </div>
            <div class="flex items-center space-x-2">
                <button
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage === 1 || loading"
                    class="px-2 sm:px-3 py-1 rounded-md border border-gray-300 text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Previous
                </button>
                <span class="px-2 sm:px-3 py-1 text-xs sm:text-sm text-gray-700">
                    Page {{ currentPage }} of {{ lastPage }}
                </span>
                <button
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage === lastPage || loading"
                    class="px-2 sm:px-3 py-1 rounded-md border border-gray-300 text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
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
    pagination: {
        type: Object,
        default: () => ({
            total: 0,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
        }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

// Computed for easier access
const total = computed(() => props.pagination.total);
const currentPage = computed(() => props.pagination.currentPage);
const lastPage = computed(() => props.pagination.lastPage);
const perPage = computed(() => props.pagination.perPage);

const emit = defineEmits(['page-change', 'page-size-change', 'sort-change', 'filter-change', 'clear-filter']);

const localPageSize = ref(props.pagination.perPage);
const localFilters = ref({});
const sortColumn = ref('created_at');
const sortDirection = ref('desc');
let filterTimeout = null;

// Initialize local filters from props
onMounted(() => {
    if (props.columnFilters) {
        localFilters.value = { ...props.columnFilters };
    }
    localPageSize.value = props.pagination.perPage;
});

// Watch for external filter changes
watch(() => props.columnFilters, (newFilters) => {
    if (newFilters) {
        localFilters.value = { ...newFilters };
    }
}, { deep: true });

// Watch for external perPage changes
watch(() => props.pagination.perPage, (newPerPage) => {
    localPageSize.value = newPerPage;
});

const startIndex = computed(() => {
    if (total.value === 0) return 0;
    return (currentPage.value - 1) * perPage.value + 1;
});

const endIndex = computed(() => {
    return Math.min(currentPage.value * perPage.value, total.value);
});

const handlePageSizeChange = () => {
    emit('page-size-change', localPageSize.value);
};

const goToPage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
        emit('page-change', page);
    }
};

const handleSort = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
    emit('sort-change', { column: sortColumn.value, direction: sortDirection.value });
};

const debouncedFilter = () => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }
    filterTimeout = setTimeout(() => {
        emit('filter-change', { ...localFilters.value });
    }, 500); // 500ms debounce
};

const clearColumnFilter = (columnKey) => {
    localFilters.value[columnKey] = '';
    emit('clear-filter', columnKey);
    emit('filter-change', { ...localFilters.value });
};

const formatCellValue = (value, column) => {
    if (value === null || value === undefined) return '-';
    if (column.format && typeof column.format === 'function') {
        return column.format(value);
    }
    return value;
};
</script>




