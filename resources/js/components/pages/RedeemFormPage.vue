<template>
    <div>
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-3 sm:mb-4 gap-3">
            <div class="flex-shrink-0">
                <div class="flex items-center space-x-2 text-xs mb-1">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Redeem Form</span>
                </div>
                <h1 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900 mt-1 sm:mt-2">Redeem Form</h1>
            </div>
            
            <!-- Filter Section - In same row -->
            <div class="flex-1 lg:max-w-4xl">
                <div class="bg-white rounded-lg shadow-md p-2 sm:p-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 mb-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Customer ID</label>
                            <Input 
                                v-model="filterCustomerId"
                                placeholder="Enter Customer ID"
                                class="text-xs"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                            <DateRangePicker
                                v-model="filterDateRange"
                                placeholder="Select date range"
                                @update:modelValue="handleFilterDateRangeChange"
                            />
                        </div>
                        <div v-if="isSuperAdmin">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Company</label>
                            <div class="relative" ref="filterCompanyDropdownRef">
                                <Input 
                                    v-model="filterCompanySearchQuery"
                                    @focus="showFilterCompanyDropdown = true"
                                    @input="showFilterCompanyDropdown = true"
                                    placeholder="Search and select company..."
                                    class="text-xs"
                                />
                                <div v-if="showFilterCompanyDropdown && filteredFilterCompanies.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <ul class="py-1">
                                        <li
                                            v-for="company in filteredFilterCompanies"
                                            :key="company.id"
                                            @click="selectFilterCompany(company)"
                                            class="px-3 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                                        >
                                            {{ company.company_name }}
                                        </li>
                                        <li
                                            @click="clearFilterCompany"
                                            class="px-3 py-2 text-xs text-gray-500 hover:bg-gray-100 cursor-pointer border-t border-gray-200"
                                        >
                                            Clear Selection
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Page</label>
                            <div class="relative" ref="filterPageDropdownRef">
                                <Input 
                                    v-model="filterPageSearchQuery"
                                    @focus="showFilterPageDropdown = true"
                                    @input="showFilterPageDropdown = true"
                                    placeholder="Search and select page..."
                                    class="text-xs"
                                />
                                <div v-if="showFilterPageDropdown && filteredFilterPages.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <ul class="py-1">
                                        <li
                                            v-for="page in filteredFilterPages"
                                            :key="page.id"
                                            @click="selectFilterPage(page)"
                                            class="px-3 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                                        >
                                            {{ page.page_name }}
                                        </li>
                                        <li
                                            @click="clearFilterPage"
                                            class="px-3 py-2 text-xs text-gray-500 hover:bg-gray-100 cursor-pointer border-t border-gray-200"
                                        >
                                            Clear Selection
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end items-center gap-3 sm:gap-4">
                        <div class="text-right">
                            <div class="text-xs text-gray-600 mb-0.5">Total Deposit</div>
                            <div class="text-base sm:text-lg font-bold text-green-600">{{ formatCurrency(filteredTotalDeposit) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-600 mb-0.5">Total Redeem</div>
                            <div class="text-base sm:text-lg font-bold text-blue-600">{{ formatCurrency(filteredTotalRedeem) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Redeem Form -->
        <div v-if="canAddRedeemForm || editingRedeemForm" class="bg-white rounded-lg shadow-md p-3 sm:p-4 mb-4">
            <h2 class="text-sm sm:text-base font-semibold text-gray-900 mb-3">{{ editingRedeemForm ? 'Edit Redeem' : 'Add New Redeem' }}</h2>
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
                <!-- Row 1: Customer ID, Customer Name, Page Name, Redeem -->
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
                        <label class="block text-xs font-medium text-gray-700 mb-1">Redeem *</label>
                        <Input 
                            v-model="form.redeem" 
                            type="number"
                            step="0.01"
                            min="0"
                            required 
                            placeholder="0.00"
                            :class="errors.redeem ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.redeem" class="mt-0.5 text-xs text-red-600">{{ errors.redeem }}</p>
                    </div>
                </div>

                <!-- Row 2: Tip, Paid, Customer Tag, Payment Method -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tip</label>
                        <Input 
                            v-model="form.tip" 
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            :class="errors.tip ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.tip" class="mt-0.5 text-xs text-red-600">{{ errors.tip }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Paid</label>
                        <Input 
                            v-model="form.paid" 
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            :class="errors.paid ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.paid" class="mt-0.5 text-xs text-red-600">{{ errors.paid }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Customer Tag</label>
                        <Input 
                            v-model="form.customer_tag" 
                            placeholder="Enter Customer Tag"
                            :class="errors.customer_tag ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.customer_tag" class="mt-0.5 text-xs text-red-600">{{ errors.customer_tag }}</p>
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
                </div>

                <!-- Submit/Update Button -->
                <div class="flex justify-end space-x-2 pt-1">
                    <Button 
                        v-if="editingRedeemForm" 
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
                            {{ editingRedeemForm ? 'Updating...' : 'Submitting...' }}
                        </span>
                        <span v-else>{{ editingRedeemForm ? 'Update' : 'Submit' }}</span>
                    </Button>
                </div>
            </form>
        </div>

        <!-- DataTable with Column Filters -->
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 gap-2">
                <h2 class="text-sm sm:text-base font-semibold text-gray-900">Redeem Records</h2>
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

            <DataTable :data="filteredRedeemForms" :columns="tableColumns" :column-filters="columnFilters" :show-global-search="false" @clear-filter="handleClearFilter">
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
                <template #cell-redeem="{ row }">
                    {{ formatCurrency(row.redeem) }}
                </template>
                <template #cell-tip="{ row }">
                    {{ formatCurrency(row.tip) }}
                </template>
                <template #cell-paid="{ row }">
                    {{ formatCurrency(row.paid) }}
                </template>
                <template #cell-customer_tag="{ row }">
                    {{ row.customer_tag || '-' }}
                </template>
                <template #cell-page.page_name="{ row }">
                    {{ row.page?.page_name || '-' }}
                </template>
                <template #cell-payment_method="{ row }">
                    {{ row.payment_method?.payment_method_name || '-' }}
                </template>
                <template #cell-status="{ row }">
                    <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(row.status)">
                        {{ row.status }}
                    </span>
                </template>
                <template #cell-created_at="{ value }">
                    {{ formatDate(value) }}
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-center space-x-2">
                        <button
                            v-if="canEditRedeemForm"
                            @click="editRedeemForm(row)"
                            class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all duration-200 hover:scale-110"
                            title="Edit"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            v-if="canDeleteRedeemForm"
                            @click="deleteRedeemForm(row.id)"
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
            title="Delete Redeem Form"
            message="Are you sure you want to delete this redeem form? This action cannot be undone."
            @confirm="confirmDeleteRedeemForm"
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

const redeemForms = ref([]);
const depositForms = ref([]);
const availablePages = ref([]);
const availablePaymentMethods = ref([]);
const availableCompanies = ref([]);
const userPermissions = ref([]);
const submitting = ref(false);
const showCompanyDropdown = ref(false);
const companySearchQuery = ref('');
const companyDropdownRef = ref(null);
const currentUserRole = ref('');
const currentUserCompanyId = ref(null);
const editingRedeemForm = ref(null);
const showDeleteDialog = ref(false);
const redeemFormToDelete = ref(null);
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

// Filter section refs
const filterCustomerId = ref('');
const filterDateRange = ref({ startDate: null, endDate: null });
const filterPageId = ref(null);
const filterPageName = ref('');
const filterPageSearchQuery = ref('');
const showFilterPageDropdown = ref(false);
const filterPageDropdownRef = ref(null);
const filterCompanyId = ref(null);
const filterCompanyName = ref('');
const filterCompanySearchQuery = ref('');
const showFilterCompanyDropdown = ref(false);
const filterCompanyDropdownRef = ref(null);

const form = ref({
    customer_id: '',
    customer_name: '',
    redeem: '',
    tip: '',
    paid: '',
    customer_tag: '',
    page_id: '',
    page_name: '',
    status: 'pending',
    payment_method_id: '',
    payment_method_name: '',
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
    'redeem': '',
    'tip': '',
    'paid': '',
    'customer_tag': '',
    'page.page_name': '',
    'payment_method': '',
    'status': '',
});

const dateRangeFilter = ref({
    startDate: null,
    endDate: null,
});

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

const canAddRedeemForm = computed(() => hasPermission('add redeem form'));
const canEditRedeemForm = computed(() => hasPermission('edit redeem form'));
const canDeleteRedeemForm = computed(() => hasPermission('delete redeem form'));

const tableColumns = computed(() => {
    const columns = [
        { key: 'created_at', label: 'Created At', sortable: true, filterable: false },
        { key: 'customer.customer_id', label: 'Customer ID', sortable: true, filterable: true },
        { key: 'customer.customer_name', label: 'Customer Name', sortable: true, filterable: true },
        { key: 'redeem', label: 'Redeem', sortable: true, filterable: true },
        { key: 'tip', label: 'Tip', sortable: true, filterable: true },
        { key: 'paid', label: 'Paid', sortable: true, filterable: true },
        { key: 'customer_tag', label: 'Customer Tag', sortable: true, filterable: true },
        { key: 'page.page_name', label: 'Page Name', sortable: true, filterable: true },
        { key: 'payment_method', label: 'Payment Method', sortable: true, filterable: true },
        { key: 'status', label: 'Status', sortable: true, filterable: true },
    ];
    
    // Add Company column for super admin (after Customer Name)
    if (isSuperAdmin.value) {
        columns.splice(3, 0, { key: 'company.company_name', label: 'Company', sortable: true, filterable: true });
    }
    
    if (canEditRedeemForm.value || canDeleteRedeemForm.value) {
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

// Filter pages for filter dropdown
const filteredFilterPages = computed(() => {
    let pages = availablePages.value;
    
    // Filter by company if super admin has selected a company
    if (isSuperAdmin.value && filterCompanyId.value) {
        pages = pages.filter(page => page.company_id === filterCompanyId.value);
    } else if (!isSuperAdmin.value && currentUserCompanyId.value) {
        // For non-super admin, filter by their company
        pages = pages.filter(page => page.company_id === currentUserCompanyId.value);
    }
    
    // Filter by search query
    if (filterPageSearchQuery.value) {
        const query = filterPageSearchQuery.value.toLowerCase();
        pages = pages.filter(page =>
            page.page_name.toLowerCase().includes(query)
        );
    }
    
    return pages;
});

// Filter companies for filter dropdown
const filteredFilterCompanies = computed(() => {
    if (!filterCompanySearchQuery.value) {
        return availableCompanies.value;
    }
    const query = filterCompanySearchQuery.value.toLowerCase();
    return availableCompanies.value.filter(company =>
        company.company_name.toLowerCase().includes(query)
    );
});

// Filtered data for totals calculation
const filteredDataForTotals = computed(() => {
    let filteredRedeem = [...redeemForms.value];
    let filteredDeposit = [...depositForms.value];

    // Filter by customer ID - exact match only
    if (filterCustomerId.value) {
        const customerId = filterCustomerId.value.trim().toLowerCase();
        filteredRedeem = filteredRedeem.filter(item =>
            item.customer?.customer_id?.toLowerCase().trim() === customerId
        );
        filteredDeposit = filteredDeposit.filter(item =>
            item.customer?.customer_id?.toLowerCase().trim() === customerId
        );
    }

    // Filter by date range
    if (filterDateRange.value.startDate && filterDateRange.value.endDate) {
        const startDate = new Date(filterDateRange.value.startDate);
        startDate.setHours(0, 0, 0, 0);
        const endDate = new Date(filterDateRange.value.endDate);
        endDate.setHours(23, 59, 59, 999);
        
        filteredRedeem = filteredRedeem.filter(item => {
            const itemDate = new Date(item.created_at);
            return itemDate >= startDate && itemDate <= endDate;
        });
        filteredDeposit = filteredDeposit.filter(item => {
            const itemDate = new Date(item.created_at);
            return itemDate >= startDate && itemDate <= endDate;
        });
    }

    // Filter by page
    if (filterPageId.value) {
        filteredRedeem = filteredRedeem.filter(item => item.page_id === filterPageId.value);
        filteredDeposit = filteredDeposit.filter(item => item.page_id === filterPageId.value);
    }

    // Filter by company
    if (isSuperAdmin.value && filterCompanyId.value) {
        filteredRedeem = filteredRedeem.filter(item => item.company_id === filterCompanyId.value);
        filteredDeposit = filteredDeposit.filter(item => item.company_id === filterCompanyId.value);
    } else if (!isSuperAdmin.value && currentUserCompanyId.value) {
        // For non-super admin, filter by their company
        filteredRedeem = filteredRedeem.filter(item => item.company_id === currentUserCompanyId.value);
        filteredDeposit = filteredDeposit.filter(item => item.company_id === currentUserCompanyId.value);
    }

    return { redeem: filteredRedeem, deposit: filteredDeposit };
});

// Calculate totals - only show when customer ID is entered
const filteredTotalRedeem = computed(() => {
    // Return 0 if no customer ID is entered
    if (!filterCustomerId.value || !filterCustomerId.value.trim()) {
        return 0;
    }
    // Only sum paid column for records with status 'paid'
    return filteredDataForTotals.value.redeem
        .filter(item => item.status?.toLowerCase() === 'paid')
        .reduce((sum, item) => {
            return sum + (parseFloat(item.paid) || 0);
        }, 0);
});

const filteredTotalDeposit = computed(() => {
    // Return 0 if no customer ID is entered
    if (!filterCustomerId.value || !filterCustomerId.value.trim()) {
        return 0;
    }
    return filteredDataForTotals.value.deposit.reduce((sum, item) => {
        return sum + (parseFloat(item.deposit) || 0);
    }, 0);
});

const filteredRedeemForms = computed(() => {
    let filtered = redeemForms.value;

    if (columnFilters.value['customer.customer_id']) {
        const query = columnFilters.value['customer.customer_id'].toLowerCase();
        filtered = filtered.filter(item =>
            item.customer?.customer_id?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value['customer.customer_name']) {
        const query = columnFilters.value['customer.customer_name'].toLowerCase();
        filtered = filtered.filter(item =>
            item.customer?.customer_name?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.redeem) {
        const query = columnFilters.value.redeem.toLowerCase();
        filtered = filtered.filter(item =>
            String(item.redeem).toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.tip) {
        const query = columnFilters.value.tip.toLowerCase();
        filtered = filtered.filter(item =>
            String(item.tip).toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.paid) {
        const query = columnFilters.value.paid.toLowerCase();
        filtered = filtered.filter(item =>
            String(item.paid).toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.customer_tag) {
        const query = columnFilters.value.customer_tag.toLowerCase();
        filtered = filtered.filter(item =>
            item.customer_tag?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value['page.page_name']) {
        const query = columnFilters.value['page.page_name'].toLowerCase();
        filtered = filtered.filter(item =>
            item.page?.page_name?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.payment_method) {
        const query = columnFilters.value.payment_method.toLowerCase();
        filtered = filtered.filter(item =>
            item.payment_method?.payment_method_name?.toLowerCase().includes(query)
        );
    }

    if (columnFilters.value.status) {
        const query = columnFilters.value.status.toLowerCase();
        filtered = filtered.filter(item =>
            item.status?.toLowerCase().includes(query)
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
    if (columnKey === 'created_at') {
        dateRangeFilter.value = { startDate: null, endDate: null };
    } else if (columnFilters.value[columnKey] !== undefined) {
        columnFilters.value[columnKey] = '';
    }
};

const loadRedeemForms = async () => {
    try {
        const response = await axios.get('/redeem-forms');
        redeemForms.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error loading redeem forms:', error);
        if (window.toast) {
            window.toast.error('Error loading redeem forms');
        }
    }
};

const loadDepositForms = async () => {
    try {
        const response = await axios.get('/deposit-forms');
        depositForms.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error loading deposit forms:', error);
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

const loadUserPaymentMethods = async (companyId = null) => {
    try {
        // For super admin, if company_id is provided, load payment methods from that company
        // Otherwise, for non-super admin, load payment methods from /user/payment-methods (which filters by user's company)
        if (isSuperAdmin.value && companyId) {
            const response = await axios.get('/payment-methods', {
                params: { company_id: companyId }
            });
            availablePaymentMethods.value = response.data.data || response.data || [];
        } else {
            const response = await axios.get('/user/payment-methods');
            availablePaymentMethods.value = response.data.payment_methods || [];
        }
    } catch (error) {
        console.error('Error loading user payment methods:', error);
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

const selectPaymentMethod = (paymentMethod) => {
    form.value.payment_method_id = paymentMethod.id;
    form.value.payment_method_name = paymentMethod.payment_method_name;
    paymentMethodSearchQuery.value = paymentMethod.payment_method_name;
    showPaymentMethodDropdown.value = false;
    highlightedPaymentMethodIndex.value = -1;
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

const scrollToHighlightedPage = () => {
    if (pageDropdownList.value && highlightedPageIndex.value >= 0) {
        const listItems = pageDropdownList.value.querySelectorAll('li');
        if (listItems[highlightedPageIndex.value]) {
            listItems[highlightedPageIndex.value].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
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
        
        // Ensure status is always 'pending' for new forms
        if (!editingRedeemForm.value) {
            payload.status = 'pending';
        }
        
        if (editingRedeemForm.value) {
            // Update existing redeem form
            await axios.put(`/redeem-forms/${editingRedeemForm.value.id}`, payload);
            if (window.toast) {
                window.toast.success('Redeem form updated successfully');
            }
            editingRedeemForm.value = null;
        } else {
            // Create new redeem form - automatically set status to 'pending'
            await axios.post('/redeem-forms', payload);
            if (window.toast) {
                window.toast.success('Redeem form submitted successfully');
            }
        }
        resetForm();
        await loadRedeemForms();
    } catch (error) {
        console.error('Error submitting redeem form:', error);
        if (error.response?.status === 422 && error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            if (window.toast) {
                window.toast.error('Please fix the validation errors');
            }
        } else {
            if (window.toast) {
                window.toast.error(error.response?.data?.message || 'Error submitting redeem form');
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
        redeem: '',
        tip: '',
        paid: '',
        customer_tag: '',
        page_id: '',
        page_name: '',
        status: 'pending',
        payment_method_id: '',
        payment_method_name: '',
        company_id: '',
        company_name: '',
    };
    customerFound.value = false;
    pageSearchQuery.value = '';
    paymentMethodSearchQuery.value = '';
    companySearchQuery.value = '';
    showCompanyDropdown.value = false;
    errors.value = {};
    editingRedeemForm.value = null;
};

const editRedeemForm = (redeemForm) => {
    editingRedeemForm.value = redeemForm;
    // Populate the main form with edit data
    form.value = {
        customer_id: redeemForm.customer?.customer_id || '',
        customer_name: redeemForm.customer?.customer_name || '',
        redeem: redeemForm.redeem,
        tip: redeemForm.tip || '',
        paid: redeemForm.paid || '',
        customer_tag: redeemForm.customer_tag || '',
        page_id: redeemForm.page_id,
        page_name: redeemForm.page?.page_name || '',
        status: redeemForm.status || 'pending',
        payment_method_id: redeemForm.payment_method_id || '',
        payment_method_name: redeemForm.paymentMethod?.payment_method_name || '',
        company_id: redeemForm.company_id || '',
        company_name: redeemForm.company?.company_name || '',
    };
    customerFound.value = !!redeemForm.customer;
    pageSearchQuery.value = redeemForm.page?.page_name || '';
    paymentMethodSearchQuery.value = redeemForm.paymentMethod?.payment_method_name || '';
    if (isSuperAdmin.value && redeemForm.company) {
        companySearchQuery.value = redeemForm.company.company_name;
    }
    
    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteRedeemForm = (id) => {
    redeemFormToDelete.value = id;
    showDeleteDialog.value = true;
};

const confirmDeleteRedeemForm = async () => {
    if (!redeemFormToDelete.value) return;

    try {
        await axios.delete(`/redeem-forms/${redeemFormToDelete.value}`);
        if (window.toast) {
            window.toast.success('Redeem form deleted successfully');
        }
        await loadRedeemForms();
        redeemFormToDelete.value = null;
    } catch (error) {
        console.error('Error deleting redeem form:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error deleting redeem form');
        }
        redeemFormToDelete.value = null;
    }
};

const cancelEdit = () => {
    editingRedeemForm.value = null;
    resetForm();
};

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

const getStatusClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'in process':
            return 'bg-blue-100 text-blue-800';
        case 'paid':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Check if any filters are applied
const hasActiveFilters = computed(() => {
    // Check column filters
    const hasColumnFilters = Object.values(columnFilters.value).some(filter => filter && filter.toString().trim() !== '');
    
    // Check date range filter
    const hasDateFilter = dateRangeFilter.value && dateRangeFilter.value.length === 2;
    
    // Check filter section filters
    const hasFilterSectionFilters = filterCustomerId.value || 
                                    (filterDateRange.value && filterDateRange.value.length === 2) ||
                                    filterPageId.value ||
                                    (isSuperAdmin.value && filterCompanyId.value);
    
    return hasColumnFilters || hasDateFilter || hasFilterSectionFilters;
});

// Export to Excel function
const exportToExcel = () => {
    try {
        // Use filtered data if filters are applied, otherwise use all data
        const dataToExport = hasActiveFilters.value ? filteredRedeemForms.value : redeemForms.value;
        
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
            'Redeem': parseFloat(item.redeem || 0).toFixed(2),
            'Paid': parseFloat(item.paid || 0).toFixed(2),
            'Payment Method': item.paymentMethod?.payment_method_name || '-',
            'Page Name': item.page?.page_name || '-',
            'Status': item.status || '-',
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
        XLSX.utils.book_append_sheet(wb, ws, 'Redeem Records');
        
        // Generate filename with timestamp
        const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
        const filename = `redeem_records_${timestamp}.xlsx`;
        
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

// Filter functions
const selectFilterPage = (page) => {
    filterPageId.value = page.id;
    filterPageName.value = page.page_name;
    filterPageSearchQuery.value = page.page_name;
    showFilterPageDropdown.value = false;
};

const clearFilterPage = () => {
    filterPageId.value = null;
    filterPageName.value = '';
    filterPageSearchQuery.value = '';
    showFilterPageDropdown.value = false;
};

const selectFilterCompany = async (company) => {
    filterCompanyId.value = company.id;
    filterCompanyName.value = company.company_name;
    filterCompanySearchQuery.value = company.company_name;
    showFilterCompanyDropdown.value = false;
    
    // Reload pages and payment methods filtered by selected company (for super admin)
    if (isSuperAdmin.value) {
        await loadUserPages(company.id);
        await loadUserPaymentMethods(company.id);
        // Clear selected page filter when company changes
        filterPageId.value = null;
        filterPageName.value = '';
        filterPageSearchQuery.value = '';
    }
};

const clearFilterCompany = async () => {
    filterCompanyId.value = null;
    filterCompanyName.value = '';
    filterCompanySearchQuery.value = '';
    showFilterCompanyDropdown.value = false;
    
    // Reload all pages and payment methods (for super admin)
    if (isSuperAdmin.value) {
        await loadUserPages();
        await loadUserPaymentMethods();
        // Clear selected page filter
        filterPageId.value = null;
        filterPageName.value = '';
        filterPageSearchQuery.value = '';
    }
};

const handleFilterDateRangeChange = (range) => {
    filterDateRange.value = range || { startDate: null, endDate: null };
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
    if (filterPageDropdownRef.value && !filterPageDropdownRef.value.contains(event.target)) {
        showFilterPageDropdown.value = false;
    }
    if (filterCompanyDropdownRef.value && !filterCompanyDropdownRef.value.contains(event.target)) {
        showFilterCompanyDropdown.value = false;
    }
};

// Watch for company_id changes to reload pages and payment methods (for super admin) - form company
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

// Watch for filter company_id changes to reload pages and payment methods (for super admin) - filter company
watch(() => filterCompanyId.value, async (newCompanyId, oldCompanyId) => {
    if (isSuperAdmin.value && newCompanyId !== oldCompanyId) {
        if (newCompanyId) {
            // Reload pages and payment methods filtered by selected company
            await loadUserPages(newCompanyId);
            await loadUserPaymentMethods(newCompanyId);
        } else {
            // Reload all pages and payment methods if company is cleared
            await loadUserPages();
            await loadUserPaymentMethods();
        }
        // Clear selected page filter when company changes
        filterPageId.value = null;
        filterPageName.value = '';
        filterPageSearchQuery.value = '';
    }
});

onMounted(async () => {
    await loadUserPermissions();
    await loadUserPages();
    // Load payment methods - for non-super admin, filter by their company_id
    await loadUserPaymentMethods(isSuperAdmin.value ? null : currentUserCompanyId.value);
    if (isSuperAdmin.value) {
        await loadCompanies();
    }
    await loadRedeemForms();
    await loadDepositForms();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

