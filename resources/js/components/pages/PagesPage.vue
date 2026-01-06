<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs sm:text-sm mb-1 sm:mb-2">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Pages</span>
                </div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mt-2 sm:mt-4">Pages</h1>
            </div>
            <Button 
                v-if="canAddPage"
                @click="showAddModal = true" 
                class="bg-blue-600 hover:bg-blue-700 w-full sm:w-auto"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-xs sm:text-sm">Add New Page</span>
            </Button>
        </div>

        <DataTable :data="pagesWithSerial" :columns="tableColumns">
            <template #cell-company.company_name="{ row }">
                {{ row.company?.company_name || '-' }}
            </template>
            <template #cell-created_at="{ value }">
                {{ formatDate(value) }}
            </template>
            <template #cell-actions="{ row }">
                <div class="flex items-center justify-center space-x-2">
                    <button
                        v-if="canEditPage"
                        @click="editPage(row)"
                        class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all duration-200 hover:scale-110"
                        title="Edit Page"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button
                        v-if="canDeletePage"
                        @click="deletePage(row.id)"
                        class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all duration-200 hover:scale-110"
                        title="Delete Page"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Add/Edit Page Modal -->
        <div v-if="showAddModal || editingPage" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl p-4 sm:p-6 w-full max-w-md mx-auto border border-gray-200 max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">{{ editingPage ? 'Edit Page' : 'Add New Page' }}</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="savePage" class="space-y-3 sm:space-y-4">
                    <div v-if="isSuperAdmin">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Company *</label>
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
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Page Name</label>
                        <Input 
                            v-model="pageForm.page_name" 
                            required 
                            placeholder="Enter page name"
                            :class="errors.page_name ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.page_name" class="mt-1 text-sm text-red-600">{{ errors.page_name }}</p>
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
            title="Delete Page"
            message="Are you sure you want to delete this page? This action cannot be undone."
            @confirm="confirmDeletePage"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import DataTable from '../ui/DataTable.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import ConfirmDialog from '../ui/ConfirmDialog.vue';

const pages = ref([]);
const showAddModal = ref(false);
const editingPage = ref(null);
const userPermissions = ref([]);
const availableCompanies = ref([]);
const showCompanyDropdown = ref(false);
const companySearchQuery = ref('');
const companyDropdownRef = ref(null);
const currentUserRole = ref('');

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

const canAddPage = computed(() => hasPermission('add page'));
const canEditPage = computed(() => hasPermission('edit page'));
const canDeletePage = computed(() => hasPermission('delete page'));

const pagesWithSerial = computed(() => {
    return pages.value.map((page, index) => ({
        ...page,
        serial_number: index + 1,
    }));
});

const tableColumns = computed(() => {
    const columns = [
        { key: 'serial_number', label: 'S.No', sortable: true },
        { key: 'page_id', label: 'Page ID', sortable: true },
        { key: 'page_name', label: 'Page Name', sortable: true },
    ];
    
    // Add company name column only for super admin
    if (isSuperAdmin.value) {
        columns.push({ key: 'company.company_name', label: 'Company', sortable: true });
    }
    
    columns.push({ key: 'created_at', label: 'Created At', sortable: true });
    
    // Only add actions column if user has edit or delete permission
    if (canEditPage.value || canDeletePage.value) {
        columns.push({ key: 'actions', label: 'Actions', sortable: false });
    }
    
    return columns;
});

const pageForm = ref({
    page_name: '',
    company_id: '',
    company_name: '',
});

const errors = ref({
    page_name: '',
    company_id: '',
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

const loadPages = async () => {
    try {
        const response = await axios.get('/pages');
        pages.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error loading pages:', error);
        if (window.toast) {
            window.toast.error('Error loading pages');
        }
    }
};

const editPage = (page) => {
    editingPage.value = page;
    pageForm.value = {
        page_name: page.page_name,
        company_id: page.company_id || '',
        company_name: page.company?.company_name || '',
    };
    if (isSuperAdmin.value && page.company) {
        companySearchQuery.value = page.company.company_name;
    }
};

const clearErrors = () => {
    errors.value = {
        page_name: '',
        company_id: '',
    };
};

const savePage = async () => {
    clearErrors();
    
    // Client-side validation
    if (!pageForm.value.page_name || !pageForm.value.page_name.trim()) {
        errors.value.page_name = 'Page name is required';
        if (window.toast) {
            window.toast.error('Please fix the validation errors');
        }
        return;
    }
    
    try {
        const payload = { ...pageForm.value };
        
        // Add company_id for super admin
        if (isSuperAdmin.value && pageForm.value.company_id) {
            payload.company_id = pageForm.value.company_id;
        }
        
        if (editingPage.value) {
            await axios.put(`/pages/${editingPage.value.id}`, payload);
            if (window.toast) {
                window.toast.success('Page updated successfully');
            }
        } else {
            await axios.post('/pages', payload);
            if (window.toast) {
                window.toast.success('Page created successfully');
            }
        }
        await loadPages();
        closeModal();
    } catch (error) {
        console.error('Error saving page:', error);
        
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
                window.toast.error(error.response?.data?.message || 'Error saving page');
            }
        }
    }
};

const showDeleteDialog = ref(false);
const pageToDelete = ref(null);

const deletePage = (pageId) => {
    pageToDelete.value = pageId;
    showDeleteDialog.value = true;
};

const confirmDeletePage = async () => {
    if (!pageToDelete.value) return;
    
    try {
        await axios.delete(`/pages/${pageToDelete.value}`);
        await loadPages();
        if (window.toast) {
            window.toast.success('Page deleted successfully');
        }
        pageToDelete.value = null;
    } catch (error) {
        console.error('Error deleting page:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error deleting page');
        }
        pageToDelete.value = null;
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
    pageForm.value.company_id = company.id;
    pageForm.value.company_name = company.company_name;
    companySearchQuery.value = company.company_name;
    showCompanyDropdown.value = false;
};

const handleClickOutside = (event) => {
    if (companyDropdownRef.value && !companyDropdownRef.value.contains(event.target)) {
        showCompanyDropdown.value = false;
    }
};

const closeModal = () => {
    showAddModal.value = false;
    editingPage.value = null;
    clearErrors();
    pageForm.value = {
        page_name: '',
        company_id: '',
        company_name: '',
    };
    companySearchQuery.value = '';
    showCompanyDropdown.value = false;
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

onMounted(async () => {
    await loadUserPermissions();
    if (isSuperAdmin.value) {
        await loadCompanies();
    }
    await loadPages();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

