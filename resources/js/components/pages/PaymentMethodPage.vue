<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs sm:text-sm mb-1 sm:mb-2">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Payment Method</span>
                </div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mt-2 sm:mt-4">Payment Method</h1>
            </div>
            <Button 
                v-if="canAddPaymentMethod"
                @click="showAddModal = true" 
                class="bg-blue-600 hover:bg-blue-700 w-full sm:w-auto"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-xs sm:text-sm">Add New Payment Method</span>
            </Button>
        </div>

        <DataTable :data="paymentMethodsWithSerial" :columns="tableColumns">
            <template #cell-created_at="{ value }">
                {{ formatDate(value) }}
            </template>
            <template #cell-company.company_name="{ row }">
                {{ row.company?.company_name || '-' }}
            </template>
            <template #cell-actions="{ row }">
                <div class="flex items-center justify-center space-x-2">
                    <button
                        v-if="canEditPaymentMethod"
                        @click="editPaymentMethod(row)"
                        class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all duration-200 hover:scale-110"
                        title="Edit Payment Method"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button
                        v-if="canDeletePaymentMethod"
                        @click="deletePaymentMethod(row.id)"
                        class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all duration-200 hover:scale-110"
                        title="Delete Payment Method"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Add/Edit Payment Method Modal -->
        <div v-if="showAddModal || editingPaymentMethod" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl p-4 sm:p-6 w-full max-w-md mx-auto border border-gray-200 max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">{{ editingPaymentMethod ? 'Edit Payment Method' : 'Add New Payment Method' }}</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="savePaymentMethod" class="space-y-3 sm:space-y-4">
                    <div v-if="isSuperAdmin">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Company</label>
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
                        </div>
                        <p v-if="errors.company_id" class="mt-1 text-xs text-red-600">{{ errors.company_id }}</p>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Payment Method Name</label>
                        <Input 
                            v-model="paymentMethodForm.payment_method_name" 
                            required 
                            placeholder="Enter payment method name"
                            :class="errors.payment_method_name ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.payment_method_name" class="mt-1 text-sm text-red-600">{{ errors.payment_method_name }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
                        <Button type="button" variant="outline" @click="closeModal" class="w-full sm:w-auto">Cancel</Button>
                        <Button type="submit" class="w-full sm:w-auto">Save</Button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Confirm Delete Dialog -->
        <ConfirmDialog
            v-model:isOpen="showDeleteDialog"
            title="Delete Payment Method"
            message="Are you sure you want to delete this payment method? This action cannot be undone."
            @confirm="confirmDeletePaymentMethod"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import DataTable from '../ui/DataTable.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import ConfirmDialog from '../ui/ConfirmDialog.vue';

const paymentMethods = ref([]);
const showAddModal = ref(false);
const editingPaymentMethod = ref(null);
const userPermissions = ref([]);
const currentUserRole = ref('');
const isSuperAdmin = computed(() => currentUserRole.value === 'super admin');
const availableCompanies = ref([]);
const showCompanyDropdown = ref(false);
const companySearchQuery = ref('');
const companyDropdownRef = ref(null);

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

const canAddPaymentMethod = computed(() => hasPermission('add payment method'));
const canEditPaymentMethod = computed(() => hasPermission('edit payment method'));
const canDeletePaymentMethod = computed(() => hasPermission('delete payment method'));

const paymentMethodsWithSerial = computed(() => {
    return paymentMethods.value.map((paymentMethod, index) => ({
        ...paymentMethod,
        serial_number: index + 1,
    }));
});

const tableColumns = computed(() => {
    const columns = [
        { key: 'serial_number', label: 'S.No', sortable: true },
        { key: 'payment_method_id', label: 'Payment Method ID', sortable: true },
        { key: 'payment_method_name', label: 'Payment Method Name', sortable: true },
    ];
    
    // Add company column for super admin
    if (isSuperAdmin.value) {
        columns.push({ key: 'company.company_name', label: 'Company', sortable: true });
    }
    
    columns.push({ key: 'created_at', label: 'Created At', sortable: true });
    
    // Only add actions column if user has edit or delete permission
    if (canEditPaymentMethod.value || canDeletePaymentMethod.value) {
        columns.push({ key: 'actions', label: 'Actions', sortable: false });
    }
    
    return columns;
});

const paymentMethodForm = ref({
    payment_method_name: '',
    company_id: null,
});

const errors = ref({
    payment_method_name: '',
    company_id: '',
});

const filteredCompanies = computed(() => {
    if (!companySearchQuery.value) {
        return availableCompanies.value;
    }
    const query = companySearchQuery.value.toLowerCase();
    return availableCompanies.value.filter(company =>
        company.company_name.toLowerCase().includes(query)
    );
});

const loadPaymentMethods = async () => {
    try {
        const response = await axios.get('/payment-methods');
        paymentMethods.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error loading payment methods:', error);
        if (window.toast) {
            window.toast.error('Error loading payment methods');
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

const selectCompany = (company) => {
    paymentMethodForm.value.company_id = company.id;
    companySearchQuery.value = company.company_name;
    showCompanyDropdown.value = false;
};

const editPaymentMethod = (paymentMethod) => {
    editingPaymentMethod.value = paymentMethod;
    paymentMethodForm.value = {
        payment_method_name: paymentMethod.payment_method_name,
        company_id: paymentMethod.company_id,
    };
    if (isSuperAdmin.value && paymentMethod.company) {
        companySearchQuery.value = paymentMethod.company.company_name;
    }
};

const clearErrors = () => {
    errors.value = {
        payment_method_name: '',
        company_id: '',
    };
};

const savePaymentMethod = async () => {
    clearErrors();
    
    // Client-side validation
    if (!paymentMethodForm.value.payment_method_name || !paymentMethodForm.value.payment_method_name.trim()) {
        errors.value.payment_method_name = 'Payment method name is required';
        if (window.toast) {
            window.toast.error('Please fix the validation errors');
        }
        return;
    }
    
    if (isSuperAdmin.value && !paymentMethodForm.value.company_id) {
        errors.value.company_id = 'Company is required';
        if (window.toast) {
            window.toast.error('Please fix the validation errors');
        }
        return;
    }
    
    try {
        if (editingPaymentMethod.value) {
            await axios.put(`/payment-methods/${editingPaymentMethod.value.id}`, paymentMethodForm.value);
            if (window.toast) {
                window.toast.success('Payment method updated successfully');
            }
        } else {
            await axios.post('/payment-methods', paymentMethodForm.value);
            if (window.toast) {
                window.toast.success('Payment method created successfully');
            }
        }
        await loadPaymentMethods();
        closeModal();
    } catch (error) {
        console.error('Error saving payment method:', error);
        
        // Handle validation errors from server
        if (error.response?.status === 422 && error.response?.data?.errors) {
            const validationErrors = error.response.data.errors;
            Object.keys(validationErrors).forEach(key => {
                if (errors.value.hasOwnProperty(key)) {
                    errors.value[key] = Array.isArray(validationErrors[key]) 
                        ? validationErrors[key][0] 
                        : validationErrors[key];
                }
            });
            
            if (window.toast) {
                window.toast.error('Please fix the validation errors');
            }
        } else {
            if (window.toast) {
                window.toast.error(error.response?.data?.message || 'Error saving payment method');
            }
        }
    }
};

const showDeleteDialog = ref(false);
const paymentMethodToDelete = ref(null);

const deletePaymentMethod = (paymentMethodId) => {
    paymentMethodToDelete.value = paymentMethodId;
    showDeleteDialog.value = true;
};

const confirmDeletePaymentMethod = async () => {
    if (!paymentMethodToDelete.value) return;
    
    try {
        await axios.delete(`/payment-methods/${paymentMethodToDelete.value}`);
        await loadPaymentMethods();
        if (window.toast) {
            window.toast.success('Payment method deleted successfully');
        }
        paymentMethodToDelete.value = null;
    } catch (error) {
        console.error('Error deleting payment method:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error deleting payment method');
        }
        paymentMethodToDelete.value = null;
    }
};


const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
};

const goToDashboard = () => {
    window.location.href = '/dashboard';
};

const loadUserPermissions = async () => {
    try {
        const response = await axios.get('/user/permissions');
        userPermissions.value = response.data.permissions || [];
        currentUserRole.value = response.data.user?.role || '';
    } catch (error) {
        console.error('Error loading permissions:', error);
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
    }
};

const closeModal = () => {
    showAddModal.value = false;
    editingPaymentMethod.value = null;
    paymentMethodForm.value = {
        payment_method_name: '',
        company_id: null,
    };
    companySearchQuery.value = '';
    showCompanyDropdown.value = false;
    clearErrors();
};

const handleClickOutside = (event) => {
    if (companyDropdownRef.value && !companyDropdownRef.value.contains(event.target)) {
        showCompanyDropdown.value = false;
    }
};

onMounted(async () => {
    await loadUserPermissions();
    if (isSuperAdmin.value) {
        await loadCompanies();
    }
    await loadPaymentMethods();
    document.addEventListener('click', handleClickOutside);
});
</script>

