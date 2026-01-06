<template>
    <div>
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-3 sm:mb-4 gap-3">
            <div class="flex-shrink-0">
                <div class="flex items-center space-x-2 text-xs mb-1">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Redeem Process</span>
                </div>
                <h1 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900 mt-1 sm:mt-2">Redeem Process</h1>
            </div>
        </div>

        <!-- DataTable with Column Filters -->
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 gap-2">
                <h2 class="text-sm sm:text-base font-semibold text-gray-900">Pending Redeem Entries (Not Paid)</h2>
            </div>

            <DataTable :data="filteredRedeemForms" :columns="tableColumns" :column-filters="columnFilters" :show-global-search="false" @clear-filter="handleClearFilter">
                <template #filter-created_at>
                    <DateRangePicker
                        v-model="dateRangeFilter"
                        placeholder="Search by date"
                        @update:modelValue="handleDateRangeChange"
                    />
                </template>
                <template #cell-created_at="{ value, row }">
                    <div @click.stop="openRedeemDialog(row)" class="cursor-pointer">
                        {{ formatDate(value) }}
                    </div>
                </template>
                <template #cell-customer.customer_name="{ row }">
                    <div @click.stop="openRedeemDialog(row)" class="cursor-pointer">
                        {{ row.customer?.customer_name || '-' }}
                    </div>
                </template>
                <template #cell-company.company_name="{ row }">
                    <div @click.stop="openRedeemDialog(row)" class="cursor-pointer">
                        {{ row.company?.company_name || '-' }}
                    </div>
                </template>
                <template #cell-paid="{ row }">
                    <div @click.stop="openRedeemDialog(row)" class="cursor-pointer">
                        {{ formatCurrency(row.paid) }}
                    </div>
                </template>
                <template #cell-customer_tag="{ row }">
                    <div @click.stop="openRedeemDialog(row)" class="cursor-pointer">
                        {{ row.customer_tag || '-' }}
                    </div>
                </template>
                <template #cell-payment_method="{ row }">
                    <div @click.stop="openRedeemDialog(row)" class="cursor-pointer">
                        {{ row.payment_method?.payment_method_name || '-' }}
                    </div>
                </template>
                <template #cell-status="{ row }">
                    <div @click.stop="openRedeemDialog(row)" class="cursor-pointer">
                        <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(row.status)">
                            {{ row.status }}
                        </span>
                    </div>
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-center" @click.stop>
                        <button
                            v-if="row.status?.toLowerCase() === 'pending'"
                            @click="processRedeemForm(row)"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 text-xs font-medium"
                            title="Process"
                        >
                            Process
                        </button>
                        <button
                            v-if="row.status?.toLowerCase() === 'in process'"
                            @click="markAsPaidDirect(row)"
                            class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-all duration-200 text-xs font-medium"
                            title="Mark as Paid"
                        >
                            Paid
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Redeem Customer Info Dialog -->
        <div v-if="showRedeemDialog" class="fixed inset-0 bg-transparent z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Redeem Customer Info</h2>
                    <button @click="closeRedeemDialog" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer ID</label>
                        <div class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                            {{ currentRedeemForm?.customer?.customer_id || '-' }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                        <div class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                            {{ currentRedeemForm?.customer?.customer_name || '-' }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Redeem</label>
                        <div class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                            {{ formatCurrency(currentRedeemForm?.paid || 0) }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <div class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                            {{ currentRedeemForm?.payment_method?.payment_method_name || '-' }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Tag</label>
                        <div class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                            {{ currentRedeemForm?.customer_tag || '-' }}
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <Button 
                        v-if="currentRedeemForm?.status?.toLowerCase() !== 'pending'"
                        @click="markAsPending" 
                        :disabled="submitting"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2"
                    >
                        <span v-if="submitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                        <span v-else>Pending</span>
                    </Button>
                    <Button 
                        @click="markAsPaid" 
                        :disabled="submitting"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2"
                    >
                        <span v-if="submitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                        <span v-else>Paid</span>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import DataTable from '../ui/DataTable.vue';
import Button from '../ui/Button.vue';
import DateRangePicker from '../ui/DateRangePicker.vue';

const redeemForms = ref([]);
const userPermissions = ref([]);
const submitting = ref(false);
const currentUserRole = ref('');
const currentUserCompanyId = ref(null);
const showRedeemDialog = ref(false);
const currentRedeemForm = ref(null);

const isSuperAdmin = computed(() => currentUserRole.value === 'super admin');


const columnFilters = ref({
    'customer.customer_name': '',
    'paid': '',
    'customer_tag': '',
    'payment_method': '',
    'status': '',
});

const dateRangeFilter = ref({
    startDate: null,
    endDate: null,
});



const tableColumns = computed(() => {
    const columns = [
        { key: 'created_at', label: 'Created At', sortable: true, filterable: false },
        { key: 'customer.customer_name', label: 'Customer Name', sortable: true, filterable: true },
        { key: 'paid', label: 'Need to Pay', sortable: true, filterable: true },
        { key: 'customer_tag', label: 'Customer Tag', sortable: true, filterable: true },
        { key: 'payment_method', label: 'Payment Method', sortable: true, filterable: true },
        { key: 'status', label: 'Status', sortable: true, filterable: true },
    ];
    
    // Add Company column for super admin (after Customer Name)
    if (isSuperAdmin.value) {
        columns.splice(2, 0, { key: 'company.company_name', label: 'Company', sortable: true, filterable: true });
    }
    
    columns.push({ key: 'actions', label: 'Actions', sortable: false, filterable: false });
    
    return columns;
});

const filteredRedeemForms = computed(() => {
    let filtered = redeemForms.value;

    // Filter to show only entries with status 'pending' or 'in process'
    filtered = filtered.filter(item => {
        const status = item.status?.toLowerCase();
        return status === 'pending' || status === 'in process';
    });

    if (columnFilters.value['customer.customer_name']) {
        const query = columnFilters.value['customer.customer_name'].toLowerCase();
        filtered = filtered.filter(item =>
            item.customer?.customer_name?.toLowerCase().includes(query)
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
        const response = await axios.get('/redeem-forms/not-paid');
        redeemForms.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error loading redeem forms:', error);
        if (window.toast) {
            window.toast.error('Error loading redeem forms');
        }
    }
};

const processRedeemForm = async (redeemForm) => {
    try {
        submitting.value = true;
        // Update status to "in process"
        const payload = {
            customer_id: redeemForm.customer?.customer_id || '',
            customer_name: redeemForm.customer?.customer_name || '',
            redeem: redeemForm.redeem,
            tip: redeemForm.tip || 0,
            paid: redeemForm.paid || 0,
            customer_tag: redeemForm.customer_tag || '',
            page_id: redeemForm.page_id,
            status: 'in process',
            payment_method_id: redeemForm.payment_method_id || null,
        };
        
        await axios.put(`/redeem-forms/${redeemForm.id}`, payload);
        
        // Set current redeem form and show dialog
        currentRedeemForm.value = { ...redeemForm, status: 'in process' };
        showRedeemDialog.value = true;
        
        // Reload the list to reflect the status change
        await loadRedeemForms();
        
        if (window.toast) {
            window.toast.success('Redeem form moved to process');
        }
    } catch (error) {
        console.error('Error processing redeem form:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error processing redeem form');
        }
    } finally {
        submitting.value = false;
    }
};

const markAsPaid = async () => {
    if (!currentRedeemForm.value) return;
    
    try {
        submitting.value = true;
        const payload = {
            customer_id: currentRedeemForm.value.customer?.customer_id || '',
            customer_name: currentRedeemForm.value.customer?.customer_name || '',
            redeem: currentRedeemForm.value.redeem,
            tip: currentRedeemForm.value.tip || 0,
            paid: currentRedeemForm.value.paid || 0,
            customer_tag: currentRedeemForm.value.customer_tag || '',
            page_id: currentRedeemForm.value.page_id,
            status: 'paid',
            payment_method_id: currentRedeemForm.value.payment_method_id || null,
        };
        
        await axios.put(`/redeem-forms/${currentRedeemForm.value.id}`, payload);
        
        if (window.toast) {
            window.toast.success('Redeem form marked as paid');
        }
        
        // Close dialog and reload list (entry will be hidden as it's now paid)
        showRedeemDialog.value = false;
        currentRedeemForm.value = null;
        await loadRedeemForms();
    } catch (error) {
        console.error('Error marking as paid:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error marking as paid');
        }
    } finally {
        submitting.value = false;
    }
};

const openRedeemDialog = (redeemForm) => {
    currentRedeemForm.value = redeemForm;
    showRedeemDialog.value = true;
};

const closeRedeemDialog = () => {
    showRedeemDialog.value = false;
    currentRedeemForm.value = null;
};

const markAsPending = async () => {
    if (!currentRedeemForm.value) return;
    
    try {
        submitting.value = true;
        const payload = {
            customer_id: currentRedeemForm.value.customer?.customer_id || '',
            customer_name: currentRedeemForm.value.customer?.customer_name || '',
            redeem: currentRedeemForm.value.redeem,
            tip: currentRedeemForm.value.tip || 0,
            paid: currentRedeemForm.value.paid || 0,
            customer_tag: currentRedeemForm.value.customer_tag || '',
            page_id: currentRedeemForm.value.page_id,
            status: 'pending',
            payment_method_id: currentRedeemForm.value.payment_method_id || null,
        };
        
        await axios.put(`/redeem-forms/${currentRedeemForm.value.id}`, payload);
        
        if (window.toast) {
            window.toast.success('Redeem form status changed to pending');
        }
        
        // Close dialog and reload list
        showRedeemDialog.value = false;
        currentRedeemForm.value = null;
        await loadRedeemForms();
    } catch (error) {
        console.error('Error changing status to pending:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error changing status to pending');
        }
    } finally {
        submitting.value = false;
    }
};

const markAsPaidDirect = async (redeemForm) => {
    try {
        submitting.value = true;
        const payload = {
            customer_id: redeemForm.customer?.customer_id || '',
            customer_name: redeemForm.customer?.customer_name || '',
            redeem: redeemForm.redeem,
            tip: redeemForm.tip || 0,
            paid: redeemForm.paid || 0,
            customer_tag: redeemForm.customer_tag || '',
            page_id: redeemForm.page_id,
            status: 'paid',
            payment_method_id: redeemForm.payment_method_id || null,
        };
        
        await axios.put(`/redeem-forms/${redeemForm.id}`, payload);
        
        if (window.toast) {
            window.toast.success('Redeem form marked as paid');
        }
        
        // Reload list (entry will be hidden as it's now paid)
        await loadRedeemForms();
    } catch (error) {
        console.error('Error marking as paid:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error marking as paid');
        }
    } finally {
        submitting.value = false;
    }
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

onMounted(async () => {
    await loadUserPermissions();
    await loadRedeemForms();
});
</script>

