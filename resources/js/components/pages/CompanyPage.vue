<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs sm:text-sm mb-1 sm:mb-2">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Companies</span>
                </div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mt-2 sm:mt-4">Companies</h1>
            </div>
            <Button 
                v-if="canAddCompany"
                @click="showAddModal = true" 
                class="bg-blue-600 hover:bg-blue-700 w-full sm:w-auto"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-xs sm:text-sm">Add New Company</span>
            </Button>
        </div>

        <DataTable :data="companiesWithSerial" :columns="tableColumns">
            <template #cell-created_at="{ value }">
                {{ formatDate(value) }}
            </template>
            <template #cell-actions="{ row }">
                <div class="flex items-center justify-center space-x-2">
                    <button
                        v-if="canEditCompany"
                        @click="editCompany(row)"
                        class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all duration-200 hover:scale-110"
                        title="Edit Company"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button
                        v-if="canDeleteCompany"
                        @click="deleteCompany(row.id)"
                        class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all duration-200 hover:scale-110"
                        title="Delete Company"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Add/Edit Company Modal -->
        <div v-if="showAddModal || editingCompany" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl p-4 sm:p-6 w-full max-w-md mx-auto border border-gray-200 max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">{{ editingCompany ? 'Edit Company' : 'Add New Company' }}</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveCompany" class="space-y-3 sm:space-y-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Company Name</label>
                        <Input 
                            v-model="companyForm.company_name" 
                            required 
                            placeholder="Enter company name"
                            :class="errors.company_name ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.company_name" class="mt-1 text-sm text-red-600">{{ errors.company_name }}</p>
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
            title="Delete Company"
            message="Are you sure you want to delete this company? This action cannot be undone."
            @confirm="confirmDeleteCompany"
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

const companies = ref([]);
const showAddModal = ref(false);
const editingCompany = ref(null);
const userPermissions = ref([]);
const currentUserRole = ref('');

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

// Only super admin can manage companies
const isSuperAdmin = computed(() => currentUserRole.value === 'super admin');

const canAddCompany = computed(() => isSuperAdmin.value && hasPermission('add company'));
const canEditCompany = computed(() => isSuperAdmin.value && hasPermission('edit company'));
const canDeleteCompany = computed(() => isSuperAdmin.value && hasPermission('delete company'));

const companiesWithSerial = computed(() => {
    return companies.value.map((company, index) => ({
        ...company,
        serial_number: index + 1,
    }));
});

const tableColumns = computed(() => {
    const columns = [
        { key: 'serial_number', label: 'S.No', sortable: true },
        { key: 'company_id', label: 'Company ID', sortable: true },
        { key: 'company_name', label: 'Company Name', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
    ];
    
    // Only add actions column if user has edit or delete permission
    if (canEditCompany.value || canDeleteCompany.value) {
        columns.push({ key: 'actions', label: 'Actions', sortable: false });
    }
    
    return columns;
});

const companyForm = ref({
    company_name: '',
});

const errors = ref({
    company_name: '',
});

const loadCompanies = async () => {
    try {
        const response = await axios.get('/companies');
        companies.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error loading companies:', error);
        if (window.toast) {
            window.toast.error('Error loading companies');
        }
    }
};

const editCompany = (company) => {
    editingCompany.value = company;
    companyForm.value = {
        company_name: company.company_name,
    };
};

const clearErrors = () => {
    errors.value = {
        company_name: '',
    };
};

const saveCompany = async () => {
    clearErrors();
    
    // Client-side validation
    if (!companyForm.value.company_name || !companyForm.value.company_name.trim()) {
        errors.value.company_name = 'Company name is required';
        if (window.toast) {
            window.toast.error('Please fix the validation errors');
        }
        return;
    }
    
    try {
        if (editingCompany.value) {
            await axios.put(`/companies/${editingCompany.value.id}`, companyForm.value);
            if (window.toast) {
                window.toast.success('Company updated successfully');
            }
        } else {
            await axios.post('/companies', companyForm.value);
            if (window.toast) {
                window.toast.success('Company created successfully');
            }
        }
        await loadCompanies();
        closeModal();
    } catch (error) {
        console.error('Error saving company:', error);
        
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
                window.toast.error(error.response?.data?.message || 'Error saving company');
            }
        }
    }
};

const showDeleteDialog = ref(false);
const companyToDelete = ref(null);

const deleteCompany = (companyId) => {
    companyToDelete.value = companyId;
    showDeleteDialog.value = true;
};

const confirmDeleteCompany = async () => {
    if (!companyToDelete.value) return;
    
    try {
        await axios.delete(`/companies/${companyToDelete.value}`);
        await loadCompanies();
        if (window.toast) {
            window.toast.success('Company deleted successfully');
        }
        companyToDelete.value = null;
    } catch (error) {
        console.error('Error deleting company:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error deleting company');
        }
        companyToDelete.value = null;
    }
};

const closeModal = () => {
    showAddModal.value = false;
    editingCompany.value = null;
    clearErrors();
    companyForm.value = {
        company_name: '',
    };
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
        
        // Redirect if not super admin
        if (currentUserRole.value !== 'super admin') {
            if (window.toast) {
                window.toast.error('Unauthorized. Only super admin can access companies.');
            }
            window.location.href = '/dashboard';
        }
    } catch (error) {
        console.error('Error loading permissions:', error);
        if (error.response?.status === 401) {
            window.location.href = '/login';
        } else if (error.response?.status === 403) {
            if (window.toast) {
                window.toast.error('Unauthorized. Only super admin can access companies.');
            }
            window.location.href = '/dashboard';
        }
    }
};

onMounted(async () => {
    await loadUserPermissions();
    if (isSuperAdmin.value) {
        await loadCompanies();
    }
});
</script>

