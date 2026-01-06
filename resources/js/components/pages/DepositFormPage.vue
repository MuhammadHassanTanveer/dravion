<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4 gap-3">
            <div>
                <div class="flex items-center space-x-2 text-xs mb-1">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Deposit Form</span>
                </div>
                <h1 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900 mt-1 sm:mt-2">Deposit Form</h1>
            </div>
        </div>

        <!-- Deposit Form -->
        <div v-if="canAddDepositForm || editingDepositForm" class="bg-white rounded-lg shadow-md p-3 sm:p-4 mb-4">
            <h2 class="text-sm sm:text-base font-semibold text-gray-900 mb-3">{{ editingDepositForm ? 'Edit Deposit' : 'Add New Deposit' }}</h2>
            <form @submit.prevent="submitForm" class="space-y-3">
                <!-- Company Field (Super Admin Only) -->
                <div v-if="isSuperAdmin" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Company *</label>
                        <div class="relative" ref="companyDropdownRef">
                            <Input 
                                v-model="companySearchQuery"
                                @focus="showCompanyDropdown = true"
                                @input="showCompanyDropdown = true"
                                required 
                                placeholder="Search and select company..."
                                :class="errors.company_id ? 'border-red-500 focus:ring-red-500' : ''"
                            />
                            <div v-if="showCompanyDropdown && filteredCompanies.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <ul class="py-1">
                                    <li
                                        v-for="company in filteredCompanies"
                                        :key="company.id"
                                        @click="selectCompany(company)"
                                        class="px-3 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                                    >
                                        {{ company.company_name }}
                                    </li>
                                </ul>
                            </div>
                            <p v-if="errors.company_id" class="mt-0.5 text-xs text-red-600">{{ errors.company_id }}</p>
                        </div>
                    </div>
                </div>
                <!-- Row 1: Customer ID, Customer Name, Deposit, Bonus -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Customer ID *</label>
                        <Input 
                            v-model="form.customer_id" 
                            @input="searchCustomer"
                            @blur="handleCustomerIdBlur"
                            required 
                            placeholder="Enter Customer ID"
                            :class="errors.customer_id ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.customer_id" class="mt-0.5 text-xs text-red-600">{{ errors.customer_id }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Customer Name *</label>
                        <Input 
                            v-model="form.customer_name" 
                            :readonly="customerFound"
                            required 
                            placeholder="Enter Customer Name"
                            :class="[
                                errors.customer_name ? 'border-red-500 focus:ring-red-500' : '',
                                customerFound ? 'bg-gray-100 cursor-not-allowed' : ''
                            ]"
                        />
                        <p v-if="errors.customer_name" class="mt-0.5 text-xs text-red-600">{{ errors.customer_name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Deposit *</label>
                        <Input 
                            v-model="form.deposit" 
                            type="number"
                            step="0.01"
                            min="0"
                            required 
                            placeholder="0.00"
                            :class="errors.deposit ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.deposit" class="mt-0.5 text-xs text-red-600">{{ errors.deposit }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Bonus</label>
                        <Input 
                            v-model="form.bonus" 
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            :class="errors.bonus ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.bonus" class="mt-0.5 text-xs text-red-600">{{ errors.bonus }}</p>
                    </div>
                </div>

                <!-- Row 2: Game ID, Page Name, Payment Method -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Game ID</label>
                        <Input 
                            v-model="form.game_id" 
                            placeholder="Enter Game ID"
                            :class="errors.game_id ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.game_id" class="mt-0.5 text-xs text-red-600">{{ errors.game_id }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Page Name *</label>
                        <div class="relative" ref="pageDropdownRef">
                            <Input 
                                v-model="pageSearchQuery"
                                @focus="showPageDropdown = true; highlightedPageIndex = -1"
                                @input="showPageDropdown = true; highlightedPageIndex = -1"
                                @keydown="handlePageDropdownKeydown"
                                required 
                                placeholder="Search and select page..."
                                :class="errors.page_id ? 'border-red-500 focus:ring-red-500' : ''"
                            />
                            <div v-if="showPageDropdown && filteredPages.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <ul class="py-1" ref="pageDropdownList">
                                    <li
                                        v-for="(page, index) in filteredPages"
                                        :key="page.id"
                                        @click="selectPage(page)"
                                        @mouseenter="highlightedPageIndex = index"
                                        :class="[
                                            'px-3 py-2 text-xs text-gray-700 cursor-pointer',
                                            highlightedPageIndex === index ? 'bg-blue-100' : 'hover:bg-gray-100'
                                        ]"
                                    >
                                        {{ page.page_name }}
                                    </li>
                                </ul>
                            </div>
                            <p v-if="errors.page_id" class="mt-0.5 text-xs text-red-600">{{ errors.page_id }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Payment Method</label>
                        <div class="relative" ref="paymentMethodDropdownRef">
                            <Input 
                                v-model="paymentMethodSearchQuery"
                                @focus="showPaymentMethodDropdown = true; highlightedPaymentMethodIndex = -1"
                                @input="showPaymentMethodDropdown = true; highlightedPaymentMethodIndex = -1"
                                @keydown="handlePaymentMethodDropdownKeydown"
                                placeholder="Search and select payment method..."
                                :class="errors.payment_method_id ? 'border-red-500 focus:ring-red-500' : ''"
                            />
                            <div v-if="showPaymentMethodDropdown && filteredPaymentMethods.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <ul class="py-1" ref="paymentMethodDropdownList">
                                    <li
                                        v-for="(paymentMethod, index) in filteredPaymentMethods"
                                        :key="paymentMethod.id"
                                        @click="selectPaymentMethod(paymentMethod)"
                                        @mouseenter="highlightedPaymentMethodIndex = index"
                                        :class="[
                                            'px-3 py-2 text-xs text-gray-700 cursor-pointer',
                                            highlightedPaymentMethodIndex === index ? 'bg-blue-100' : 'hover:bg-gray-100'
                                        ]"
                                    >
                                        {{ paymentMethod.payment_method_name }}
                                    </li>
                                </ul>
                            </div>
                            <p v-if="errors.payment_method_id" class="mt-0.5 text-xs text-red-600">{{ errors.payment_method_id }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Remarks</label>
                        <textarea
                            v-model="form.remarks"
                            rows="2"
                            class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="Enter remarks (optional)"
                        ></textarea>
                        <p v-if="errors.remarks" class="mt-0.5 text-xs text-red-600">{{ errors.remarks }}</p>
                    </div>
                </div>

                <!-- Submit/Update Button -->
                <div class="flex justify-end space-x-2 pt-1">
                    <Button 
                        v-if="editingDepositForm" 
                        type="button" 
                        @click="cancelEdit" 
                        variant="outline" 
                        class="text-xs px-4 py-1.5"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="submitting" class="bg-blue-600 hover:bg-blue-700 text-xs px-4 py-1.5">
                        <span v-if="submitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ editingDepositForm ? 'Updating...' : 'Submitting...' }}
                        </span>
                        <span v-else>{{ editingDepositForm ? 'Update' : 'Submit' }}</span>
                    </Button>
                </div>
            </form>
        </div>

        <!-- DataTable with Column Filters -->
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 gap-2">
                <h2 class="text-sm sm:text-base font-semibold text-gray-900">Deposit Records</h2>
                <Button
                    @click="exportToExcel"
                    class="bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm px-3 py-2 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export to Excel
                </Button>
            </div>

            <DataTable :data="filteredDepositForms" :columns="tableColumns" :column-filters="columnFilters" :show-global-search="false" @clear-filter="handleClearFilter">
                <template #filter-created_at>
                    <DateRangePicker
                        v-model="dateRangeFilter"
                        placeholder="Search by date"
                        @update:modelValue="handleDateRangeChange"
                    />
                </template>
                <template #cell-customer.customer_id="{ row }">
                    {{ row.customer?.customer_id || '-' }}
                </template>
                <template #cell-customer.customer_name="{ row }">
                    {{ row.customer?.customer_name || '-' }}
                </template>
                <template #cell-company.company_name="{ row }">
                    {{ row.company?.company_name || '-' }}
                </template>
                <template #cell-deposit="{ row }">
                    {{ formatCurrency(row.deposit) }}
                </template>
                <template #cell-bonus="{ row }">
                    {{ formatCurrency(row.bonus) }}
                </template>
                <template #cell-page.page_name="{ row }">
                    {{ row.page?.page_name || '-' }}
                </template>
                <template #cell-payment_method.payment_method_name="{ row }">
                    {{ row.payment_method?.payment_method_name || '-' }}
                </template>
                <template #cell-created_at="{ value }">
                    {{ formatDate(value) }}
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-center space-x-2">
                        <button
                            v-if="canEditDepositForm"
                            @click="editDepositForm(row)"
                            class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all duration-200 hover:scale-110"
                            title="Edit"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            v-if="canDeleteDepositForm"
                            @click="deleteDepositForm(row.id)"
                            class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all duration-200 hover:scale-110"
                            title="Delete"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Confirm Delete Dialog -->
        <ConfirmDialog
            v-model:isOpen="showDeleteDialog"
            title="Delete Deposit Form"
            message="Are you sure you want to delete this deposit form? This action cannot be undone."
            @confirm="confirmDeleteDepositForm"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import * as XLSX from 'xlsx';
import DataTable from '../ui/DataTable.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import ConfirmDialog from '../ui/ConfirmDialog.vue';
import DateRangePicker from '../ui/DateRangePicker.vue';

const depositForms = ref([]);
const availablePages = ref([]);
const availableCompanies = ref([]);
const userPermissions = ref([]);
const submitting = ref(false);
const editingDepositForm = ref(null);
const showDeleteDialog = ref(false);
const depositFormToDelete = ref(null);
const customerFound = ref(false);
const showPageDropdown = ref(false);
const pageSearchQuery = ref('');
const pageDropdownRef = ref(null);
const pageDropdownList = ref(null);
const highlightedPageIndex = ref(-1);
const showPaymentMethodDropdown = ref(false);
const paymentMethodSearchQuery = ref('');
const paymentMethodDropdownRef = ref(null);
const paymentMethodDropdownList = ref(null);
const highlightedPaymentMethodIndex = ref(-1);
const showCompanyDropdown = ref(false);
const companySearchQuery = ref('');
const companyDropdownRef = ref(null);
const currentUserRole = ref('');
const currentUserCompanyId = ref(null);
const availablePaymentMethods = ref([]);

const form = ref({
    customer_id: '',
    customer_name: '',
    deposit: '',
    bonus: '',
    game_id: '',
    page_id: '',
    page_name: '',
    payment_method_id: '',
    payment_method_name: '',
    remarks: '',
    company_id: '',
    company_name: '',
});

const isSuperAdmin = computed(() => currentUserRole.value === 'super admin');

const filteredCompanies = computed(() => {
    if (!companySearchQuery.value) {
        return availableCompanies.value;
    }
    const query = companySearchQuery.value.toLowerCase();
    return availableCompanies.value.filter(company =>
        company.company_name.toLowerCase().includes(query)
    );
});

const errors = ref({});

const columnFilters = ref({
    'customer.customer_id': '',
    'customer.customer_name': '',
    'deposit': '',
    'bonus': '',
    'game_id': '',
    'page.page_name': '',
    'payment_method.payment_method_name': '',
    'remarks': '',
    // Company filter will be added conditionally in onMounted
});

const dateRangeFilter = ref({
    startDate: null,
    endDate: null,
});

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

const canAddDepositForm = computed(() => hasPermission('add deposit form'));
const canEditDepositForm = computed(() => hasPermission('edit deposit form'));
const canDeleteDepositForm = computed(() => hasPermission('delete deposit form'));

const tableColumns = computed(() => {
    const columns = [
        { key: 'created_at', label: 'Created At', sortable: true, filterable: false },
        { key: 'customer.customer_id', label: 'Customer ID', sortable: true, filterable: true },
        { key: 'customer.customer_name', label: 'Customer Name', sortable: true, filterable: true },
        { key: 'deposit', label: 'Deposit', sortable: true, filterable: true },
        { key: 'bonus', label: 'Bonus', sortable: true, filterable: true },
        { key: 'game_id', label: 'Game ID', sortable: true, filterable: true },
        { key: 'page.page_name', label: 'Page Name', sortable: true, filterable: true },
        { key: 'payment_method.payment_method_name', label: 'Payment Method', sortable: true, filterable: true },
        { key: 'remarks', label: 'Remarks', sortable: true, filterable: true },
    ];
    
    // Add company column only for super admin
    if (isSuperAdmin.value) {
        // Insert company column after Customer Name
        columns.splice(3, 0, { key: 'company.company_name', label: 'Company', sortable: true, filterable: true });
    }
    
    if (canEditDepositForm.value || canDeleteDepositForm.value) {
        columns.push({ key: 'actions', label: 'Actions', sortable: false, filterable: false });
    }
    
    return columns;
});

const filteredPages = computed(() => {
    if (!pageSearchQuery.value) {
        return availablePages.value;
    }
    const query = pageSearchQuery.value.toLowerCase();
    return availablePages.value.filter(page =>
        page.page_name.toLowerCase().includes(query)
    );
});

const filteredPaymentMethods = computed(() => {
    if (!paymentMethodSearchQuery.value) {
        return availablePaymentMethods.value;
    }
    const query = paymentMethodSearchQuery.value.toLowerCase();
    return availablePaymentMethods.value.filter(paymentMethod =>
        paymentMethod.payment_method_name.toLowerCase().includes(query)
    );
});

const filteredDepositForms = computed(() => {
    let filtered = depositForms.value;

    if (columnFilters.value.customer_id) {
        const query = columnFilters.value.customer_id.toLowerCase();
        filtered = filtered.filter(item =>
            item.customer?.customer_id?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.customer_name) {
        const query = columnFilters.value.customer_name.toLowerCase();
        filtered = filtered.filter(item =>
            item.customer?.customer_name?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.deposit) {
        const query = columnFilters.value.deposit.toLowerCase();
        filtered = filtered.filter(item =>
            String(item.deposit).toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.bonus) {
        const query = columnFilters.value.bonus.toLowerCase();
        filtered = filtered.filter(item =>
            String(item.bonus).toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.game_id) {
        const query = columnFilters.value.game_id.toLowerCase();
        filtered = filtered.filter(item =>
            item.game_id?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value['page.page_name']) {
        const query = columnFilters.value['page.page_name'].toLowerCase();
        filtered = filtered.filter(item =>
            item.page?.page_name?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value['payment_method.payment_method_name']) {
        const query = columnFilters.value['payment_method.payment_method_name'].toLowerCase();
        filtered = filtered.filter(item =>
            item.payment_method?.payment_method_name?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.remarks) {
        const query = columnFilters.value.remarks.toLowerCase();
        filtered = filtered.filter(item =>
            item.remarks?.toLowerCase().includes(query)
        );
    }

    // Filter by company name (only for super admin)
    if (isSuperAdmin.value && columnFilters.value['company.company_name']) {
        const query = columnFilters.value['company.company_name'].toLowerCase();
        filtered = filtered.filter(item =>
            item.company?.company_name?.toLowerCase().includes(query)
        );
    }

    // Apply date range filter for created_at
    if (dateRangeFilter.value.startDate && dateRangeFilter.value.endDate) {
        const startDate = new Date(dateRangeFilter.value.startDate);
        startDate.setHours(0, 0, 0, 0);
        const endDate = new Date(dateRangeFilter.value.endDate);
        endDate.setHours(23, 59, 59, 999);
        
        filtered = filtered.filter(item => {
            const itemDate = new Date(item.created_at);
            return itemDate >= startDate && itemDate <= endDate;
        });
    }

    return filtered;
});

const handleDateRangeChange = (value) => {
    dateRangeFilter.value = value;
};

const handleClearFilter = (columnKey) => {
    if (columnFilters.value[columnKey] !== undefined) {
        columnFilters.value[columnKey] = '';
    }
};

const loadDepositForms = async () => {
    try {
        const response = await axios.get('/deposit-forms');
        depositForms.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error loading deposit forms:', error);
        if (window.toast) {
            window.toast.error('Error loading deposit forms');
        }
    }
};

const loadUserPages = async (companyId = null) => {
    try {
        // For super admin, if company_id is provided, load pages from that company
        // Otherwise, for non-super admin, load assigned pages from /user/pages
        if (isSuperAdmin.value && companyId) {
            const response = await axios.get('/pages', {
                params: { company_id: companyId }
            });
            availablePages.value = response.data.data || response.data || [];
        } else {
            const response = await axios.get('/user/pages');
            availablePages.value = response.data.pages || [];
        }
    } catch (error) {
        console.error('Error loading user pages:', error);
    }
};

const searchCustomer = async () => {
    if (!form.value.customer_id) {
        customerFound.value = false;
        form.value.customer_name = '';
        return;
    }

    try {
        const response = await axios.get('/customers/search', {
            params: { customer_id: form.value.customer_id }
        });

        if (response.data.customer) {
            form.value.customer_name = response.data.customer.customer_name;
            customerFound.value = true;
        } else {
            customerFound.value = false;
            if (!form.value.customer_name) {
                form.value.customer_name = '';
            }
        }
    } catch (error) {
        console.error('Error searching customer:', error);
        customerFound.value = false;
    }
};

const handleCustomerIdBlur = () => {
    if (form.value.customer_id && !customerFound.value) {
        customerFound.value = false;
    }
};

const selectPage = (page) => {
    form.value.page_id = page.id;
    form.value.page_name = page.page_name;
    pageSearchQuery.value = page.page_name;
    showPageDropdown.value = false;
    highlightedPageIndex.value = -1;
};

const handlePageDropdownKeydown = (event) => {
    if (!showPageDropdown.value || filteredPages.value.length === 0) {
        return;
    }

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedPageIndex.value = Math.min(highlightedPageIndex.value + 1, filteredPages.value.length - 1);
            scrollToHighlightedPage();
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedPageIndex.value = Math.max(highlightedPageIndex.value - 1, -1);
            scrollToHighlightedPage();
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedPageIndex.value >= 0 && highlightedPageIndex.value < filteredPages.value.length) {
                selectPage(filteredPages.value[highlightedPageIndex.value]);
            } else if (filteredPages.value.length > 0) {
                // Select first item if nothing is highlighted
                selectPage(filteredPages.value[0]);
            }
            break;
        case 'Escape':
            event.preventDefault();
            showPageDropdown.value = false;
            highlightedPageIndex.value = -1;
            break;
        case 'Tab':
            // Allow Tab to work normally, but close dropdown
            showPageDropdown.value = false;
            highlightedPageIndex.value = -1;
            break;
    }
};

const selectPaymentMethod = (paymentMethod) => {
    form.value.payment_method_id = paymentMethod.id;
    form.value.payment_method_name = paymentMethod.payment_method_name;
    paymentMethodSearchQuery.value = paymentMethod.payment_method_name;
    showPaymentMethodDropdown.value = false;
    highlightedPaymentMethodIndex.value = -1;
};

const handlePaymentMethodDropdownKeydown = (event) => {
    if (!showPaymentMethodDropdown.value || filteredPaymentMethods.value.length === 0) {
        return;
    }

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedPaymentMethodIndex.value = Math.min(highlightedPaymentMethodIndex.value + 1, filteredPaymentMethods.value.length - 1);
            scrollToHighlightedPaymentMethod();
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedPaymentMethodIndex.value = Math.max(highlightedPaymentMethodIndex.value - 1, -1);
            scrollToHighlightedPaymentMethod();
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedPaymentMethodIndex.value >= 0 && highlightedPaymentMethodIndex.value < filteredPaymentMethods.value.length) {
                selectPaymentMethod(filteredPaymentMethods.value[highlightedPaymentMethodIndex.value]);
            } else if (filteredPaymentMethods.value.length > 0) {
                // Select first item if nothing is highlighted
                selectPaymentMethod(filteredPaymentMethods.value[0]);
            }
            break;
        case 'Escape':
            event.preventDefault();
            showPaymentMethodDropdown.value = false;
            highlightedPaymentMethodIndex.value = -1;
            break;
        case 'Tab':
            // Allow Tab to work normally, but close dropdown
            showPaymentMethodDropdown.value = false;
            highlightedPaymentMethodIndex.value = -1;
            break;
    }
};

const scrollToHighlightedPaymentMethod = () => {
    if (paymentMethodDropdownList.value && highlightedPaymentMethodIndex.value >= 0) {
        const listItems = paymentMethodDropdownList.value.querySelectorAll('li');
        if (listItems[highlightedPaymentMethodIndex.value]) {
            listItems[highlightedPaymentMethodIndex.value].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }
};

const loadUserPaymentMethods = async (companyId = null) => {
    try {
        // For super admin, if company_id is provided, load payment methods from that company
        // Otherwise, for non-super admin, load payment methods from /user/payment-methods (which filters by user's company)
        if (isSuperAdmin.value) {
            if (companyId) {
                // Super admin with selected company
                const response = await axios.get('/payment-methods', {
                    params: { company_id: companyId }
                });
                availablePaymentMethods.value = response.data.data || response.data || [];
                console.log('Loaded payment methods for super admin (company:', companyId, '):', availablePaymentMethods.value.length);
            } else {
                // Super admin without company selected - don't load payment methods yet
                availablePaymentMethods.value = [];
                console.log('Super admin - no company selected, payment methods cleared');
            }
        } else {
            // Non-super admin - load payment methods from their company
            const response = await axios.get('/user/payment-methods');
            availablePaymentMethods.value = response.data.payment_methods || [];
            console.log('Loaded payment methods for non-super admin:', availablePaymentMethods.value.length, availablePaymentMethods.value);
        }
    } catch (error) {
        console.error('Error loading user payment methods:', error);
        availablePaymentMethods.value = [];
    }
};

const loadCompanies = async () => {
    try {
        const response = await axios.get('/user/companies');
        availableCompanies.value = response.data.companies || [];
    } catch (error) {
        console.error('Error loading companies:', error);
    }
};

const selectCompany = async (company) => {
    form.value.company_id = company.id;
    form.value.company_name = company.company_name;
    companySearchQuery.value = company.company_name;
    showCompanyDropdown.value = false;
    
    // Reload pages and payment methods filtered by selected company (for super admin)
    if (isSuperAdmin.value) {
        await loadUserPages(company.id);
        await loadUserPaymentMethods(company.id);
        // Clear selected page and payment method when company changes
        form.value.page_id = '';
        form.value.page_name = '';
        pageSearchQuery.value = '';
        form.value.payment_method_id = '';
        form.value.payment_method_name = '';
        paymentMethodSearchQuery.value = '';
    }
};

const submitForm = async () => {
    errors.value = {};
    submitting.value = true;

    try {
        const payload = { ...form.value };
        
        // Add company_id for super admin
        if (isSuperAdmin.value && form.value.company_id) {
            payload.company_id = form.value.company_id;
        }
        
        if (editingDepositForm.value) {
            // Update existing deposit form
            await axios.put(`/deposit-forms/${editingDepositForm.value.id}`, payload);
            if (window.toast) {
                window.toast.success('Deposit form updated successfully');
            }
            editingDepositForm.value = null;
        } else {
            // Create new deposit form
            await axios.post('/deposit-forms', payload);
            if (window.toast) {
                window.toast.success('Deposit form submitted successfully');
            }
        }
        resetForm();
        await loadDepositForms();
    } catch (error) {
        console.error('Error submitting deposit form:', error);
        if (error.response?.status === 422 && error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            if (window.toast) {
                window.toast.error('Please fix the validation errors');
            }
        } else {
            if (window.toast) {
                window.toast.error(error.response?.data?.message || 'Error submitting deposit form');
            }
        }
    } finally {
        submitting.value = false;
    }
};

const resetForm = () => {
    form.value = {
        customer_id: '',
        customer_name: '',
        deposit: '',
        bonus: '',
        game_id: '',
        page_id: '',
        page_name: '',
        payment_method_id: '',
        payment_method_name: '',
        remarks: '',
        company_id: '',
        company_name: '',
    };
    customerFound.value = false;
    pageSearchQuery.value = '';
    paymentMethodSearchQuery.value = '';
    companySearchQuery.value = '';
    showCompanyDropdown.value = false;
    showPaymentMethodDropdown.value = false;
    errors.value = {};
    editingDepositForm.value = null;
};

const editDepositForm = (depositForm) => {
    editingDepositForm.value = depositForm;
    // Populate the main form with edit data
    form.value = {
        customer_id: depositForm.customer?.customer_id || '',
        customer_name: depositForm.customer?.customer_name || '',
        deposit: depositForm.deposit,
        bonus: depositForm.bonus || '',
        game_id: depositForm.game_id || '',
        page_id: depositForm.page_id,
        page_name: depositForm.page?.page_name || '',
        payment_method_id: depositForm.payment_method_id || '',
        payment_method_name: depositForm.payment_method?.payment_method_name || '',
        remarks: depositForm.remarks || '',
        company_id: depositForm.company_id || '',
        company_name: depositForm.company?.company_name || '',
    };
    customerFound.value = !!depositForm.customer;
    if (isSuperAdmin.value && depositForm.company) {
        companySearchQuery.value = depositForm.company.company_name;
    }
    pageSearchQuery.value = depositForm.page?.page_name || '';
    paymentMethodSearchQuery.value = depositForm.payment_method?.payment_method_name || '';
    
    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteDepositForm = (id) => {
    depositFormToDelete.value = id;
    showDeleteDialog.value = true;
};

const confirmDeleteDepositForm = async () => {
    if (!depositFormToDelete.value) return;

    try {
        await axios.delete(`/deposit-forms/${depositFormToDelete.value}`);
        if (window.toast) {
            window.toast.success('Deposit form deleted successfully');
        }
        await loadDepositForms();
        depositFormToDelete.value = null;
    } catch (error) {
        console.error('Error deleting deposit form:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error deleting deposit form');
        }
        depositFormToDelete.value = null;
    }
};

const cancelEdit = () => {
    editingDepositForm.value = null;
    resetForm();
};

// Filters are applied automatically by DataTable component

const formatCurrency = (value) => {
    if (!value) return '$0.00';
    return '$' + parseFloat(value).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const seconds = String(d.getSeconds()).padStart(2, '0');
    return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
};

// Check if any filters are applied
const hasActiveFilters = computed(() => {
    // Check column filters
    const hasColumnFilters = Object.values(columnFilters.value).some(filter => filter && filter.toString().trim() !== '');
    
    // Check date range filter
    const hasDateFilter = dateRangeFilter.value && dateRangeFilter.value.length === 2;
    
    return hasColumnFilters || hasDateFilter;
});

// Export to Excel function
const exportToExcel = () => {
    try {
        // Use filtered data if filters are applied, otherwise use all data
        const dataToExport = hasActiveFilters.value ? filteredDepositForms.value : depositForms.value;
        
        if (dataToExport.length === 0) {
            if (window.toast) {
                window.toast.warning('No data to export');
            } else {
                alert('No data to export');
            }
            return;
        }
        
        // Prepare data for Excel
        const excelData = dataToExport.map(item => ({
            'Customer ID': item.customer?.customer_id || '-',
            'Customer Name': item.customer?.customer_name || '-',
            'Deposit': parseFloat(item.deposit || 0).toFixed(2),
            'Bonus': parseFloat(item.bonus || 0).toFixed(2),
            'Game ID': item.game_id || '-',
            'Page Name': item.page?.page_name || '-',
            'Payment Method': item.payment_method?.payment_method_name || '-',
            'Remarks': item.remarks || '-',
            'Company': isSuperAdmin.value ? (item.company?.company_name || '-') : undefined,
            'Created At': formatDate(item.created_at),
        }));
        
        // Remove company column if not super admin
        if (!isSuperAdmin.value) {
            excelData.forEach(row => delete row.Company);
        }
        
        // Create workbook and worksheet
        const ws = XLSX.utils.json_to_sheet(excelData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Deposit Records');
        
        // Generate filename with timestamp
        const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
        const filename = `deposit_records_${timestamp}.xlsx`;
        
        // Download the file
        XLSX.writeFile(wb, filename);
        
        if (window.toast) {
            window.toast.success(`Exported ${dataToExport.length} record(s) to Excel`);
        }
    } catch (error) {
        console.error('Error exporting to Excel:', error);
        if (window.toast) {
            window.toast.error('Error exporting to Excel');
        } else {
            alert('Error exporting to Excel');
        }
    }
};

const goToDashboard = () => {
    window.location.href = '/dashboard';
};

const loadUserPermissions = async () => {
    try {
        const response = await axios.get('/user/permissions');
        userPermissions.value = response.data.permissions || [];
        currentUserRole.value = response.data.user?.role || '';
        currentUserCompanyId.value = response.data.user?.company_id || null;
    } catch (error) {
        console.error('Error loading permissions:', error);
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
    }
};

const handleClickOutside = (event) => {
    if (pageDropdownRef.value && !pageDropdownRef.value.contains(event.target)) {
        showPageDropdown.value = false;
    }
    if (paymentMethodDropdownRef.value && !paymentMethodDropdownRef.value.contains(event.target)) {
        showPaymentMethodDropdown.value = false;
    }
    if (companyDropdownRef.value && !companyDropdownRef.value.contains(event.target)) {
        showCompanyDropdown.value = false;
    }
};

// Watch for company_id changes to reload pages and payment methods (for super admin)
watch(() => form.value.company_id, async (newCompanyId, oldCompanyId) => {
    if (isSuperAdmin.value && newCompanyId && newCompanyId !== oldCompanyId) {
        // Reload pages and payment methods filtered by selected company
        await loadUserPages(newCompanyId);
        await loadUserPaymentMethods(newCompanyId);
        // Clear selected page and payment method when company changes
        form.value.page_id = '';
        form.value.page_name = '';
        pageSearchQuery.value = '';
        form.value.payment_method_id = '';
        form.value.payment_method_name = '';
        paymentMethodSearchQuery.value = '';
    }
});

onMounted(async () => {
    await loadUserPermissions();
    await loadUserPages();
    // Load payment methods - for non-super admin, this will load from their company
    // For super admin, payment methods will be loaded when they select a company
    if (!isSuperAdmin.value) {
        await loadUserPaymentMethods();
    }
    if (isSuperAdmin.value) {
        await loadCompanies();
        // Add company filter for super admin
        columnFilters.value['company.company_name'] = '';
    }
    await loadDepositForms();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

