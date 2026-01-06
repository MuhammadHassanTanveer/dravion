<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs sm:text-sm mb-1 sm:mb-2">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Users</span>
                </div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mt-2 sm:mt-4">Users</h1>
            </div>
            <Button 
                v-if="canAddUser"
                @click="showAddModal = true" 
                class="bg-blue-600 hover:bg-blue-700 w-full sm:w-auto"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-xs sm:text-sm">Add New User</span>
            </Button>
        </div>

        <DataTable :data="users" :columns="tableColumns">
            <template #cell-company.company_name="{ row }">
                {{ row.company?.company_name || '-' }}
            </template>
            <template #cell-roles="{ row }">
                <span v-for="role in row.roles" :key="role.id" class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs mr-1">
                    {{ role.name }}
                </span>
            </template>
            <template #cell-pages="{ row }">
                <div 
                    v-if="row.pages && Array.isArray(row.pages) && row.pages.length > 0"
                    @click="openPagesDialog(row)"
                    class="cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
                >
                    <div class="flex flex-wrap gap-1">
                        <span 
                            v-for="page in row.pages.slice(0, 3)" 
                            :key="page.id || page" 
                            class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs"
                        >
                            {{ typeof page === 'object' ? page.page_name : page }}
                        </span>
                    </div>
                    <div v-if="row.pages.length > 3" class="mt-1">
                        <span class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                            and {{ row.pages.length - 3 }} more (click to view all)
                        </span>
                    </div>
                </div>
                <span v-else class="text-xs text-gray-400">No pages</span>
            </template>
            <template #cell-created_at="{ value }">
                {{ formatDate(value) }}
            </template>
            <template #cell-actions="{ row }">
                <div class="flex items-center justify-center space-x-2">
                    <button
                        v-if="canEditUser"
                        @click="editUser(row)"
                        class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all duration-200 hover:scale-110"
                        title="Edit User"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button
                        v-if="canDeleteUser"
                        @click="deleteUser(row.id)"
                        class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all duration-200 hover:scale-110"
                        title="Delete User"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Add/Edit User Modal -->
        <div v-if="showAddModal || editingUser" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl p-4 sm:p-6 w-full max-w-md mx-auto border border-gray-200 max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">{{ editingUser ? 'Edit User' : 'Add New User' }}</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveUser" class="space-y-3 sm:space-y-4">
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
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">User ID</label>
                        <Input 
                            v-model="userForm.user_id" 
                            required 
                            :disabled="!!editingUser"
                            :class="errors.user_id ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.user_id" class="mt-1 text-sm text-red-600">{{ errors.user_id }}</p>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Name</label>
                        <Input 
                            v-model="userForm.name" 
                            required
                            :class="errors.name ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Password</label>
                        <Input 
                            v-model="userForm.password" 
                            type="password" 
                            :required="!editingUser"
                            :class="errors.password ? 'border-red-500 focus:ring-red-500' : ''"
                        />
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                        <p v-if="!editingUser && !errors.password" class="mt-1 text-xs text-gray-500">Password must be at least 8 characters</p>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select 
                            v-model="userForm.role" 
                            class="w-full h-10 px-3 rounded-md border"
                            :class="errors.role ? 'border-red-500 focus:ring-red-500' : 'border-gray-300'"
                        >
                            <option value="">Select Role</option>
                            <option v-for="role in availableRoles" :key="role.id" :value="role.name">{{ role.name }}</option>
                        </select>
                        <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role }}</p>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs sm:text-sm font-medium text-gray-700">Pages</label>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="selectAllPages"
                                    type="button"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 bg-blue-50 hover:bg-blue-100 rounded border border-blue-200"
                                    title="Select All Pages"
                                >
                                    Select All
                                </button>
                                <button
                                    v-if="userForm.pages && userForm.pages.length > 0"
                                    @click="clearAllPages"
                                    type="button"
                                    class="text-xs text-red-600 hover:text-red-800 font-medium px-2 py-1 bg-red-50 hover:bg-red-100 rounded border border-red-200"
                                    title="Clear All Selected Pages"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                        <MultiSelect
                            v-model="userForm.pages"
                            :items="availablePages"
                            display-key="page_name"
                            value-key="id"
                            placeholder="Search and select pages..."
                            :auto-close-on-select="true"
                            :show-select-all="true"
                        />
                        <p v-if="errors.pages" class="mt-1 text-sm text-red-600">{{ errors.pages }}</p>
                        
                        <!-- Selected Pages Display -->
                        <div v-if="userForm.pages && userForm.pages.length > 0" class="mt-2 flex flex-wrap gap-2">
                            <span
                                v-for="page in userForm.pages"
                                :key="page.id"
                                class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-blue-100 text-blue-800"
                            >
                                {{ page.page_name }}
                                <button
                                    @click="removePage(page.id)"
                                    class="ml-1 text-blue-600 hover:text-blue-800"
                                    type="button"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                        <Button type="submit">Save</Button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Confirm Delete Dialog -->
        <ConfirmDialog
            v-model:isOpen="showDeleteDialog"
            title="Delete User"
            message="Are you sure you want to delete this user? This action cannot be undone."
            @confirm="confirmDeleteUser"
        />
        
        <!-- Pages Dialog -->
        <div v-if="showPagesModal" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="showPagesModal = false">
            <div class="bg-black/50 backdrop-blur-sm fixed inset-0" @click="showPagesModal = false"></div>
            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-auto border border-gray-200 relative z-10 max-h-[80vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">Pages Assigned to {{ selectedUserName }}</h2>
                    <button @click="showPagesModal = false" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div v-if="selectedUserPages.length > 0" class="space-y-2">
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="page in selectedUserPages"
                            :key="page.id || page"
                            class="inline-flex items-center px-3 py-1.5 rounded-md text-xs sm:text-sm bg-green-100 text-green-800 font-medium"
                        >
                            {{ typeof page === 'object' ? page.page_name : page }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Total: {{ selectedUserPages.length }} page(s)</p>
                </div>
                <div v-else class="text-center py-8">
                    <p class="text-sm text-gray-500">No pages assigned</p>
                </div>
                <div class="mt-6 flex justify-end">
                    <Button @click="showPagesModal = false" variant="outline">Close</Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import Breadcrumb from '../ui/Breadcrumb.vue';
import BreadcrumbItem from '../ui/BreadcrumbItem.vue';
import BreadcrumbLink from '../ui/BreadcrumbLink.vue';
import BreadcrumbSeparator from '../ui/BreadcrumbSeparator.vue';
import DataTable from '../ui/DataTable.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import ConfirmDialog from '../ui/ConfirmDialog.vue';
import MultiSelect from '../ui/MultiSelect.vue';

const users = ref([]);
const availableRoles = ref([]);
const availablePages = ref([]);
const availableCompanies = ref([]);
const showAddModal = ref(false);
const editingUser = ref(null);
const userPermissions = ref([]);
const showPagesModal = ref(false);
const selectedUserPages = ref([]);
const selectedUserName = ref('');
const showCompanyDropdown = ref(false);
const companySearchQuery = ref('');
const companyDropdownRef = ref(null);
const currentUserRole = ref('');
const currentUserCompanyId = ref(null);

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

const canAddUser = computed(() => hasPermission('add user'));
const canEditUser = computed(() => hasPermission('edit user'));
const canDeleteUser = computed(() => hasPermission('delete user'));

const tableColumns = computed(() => {
    const columns = [
        { key: 'user_id', label: 'User ID', sortable: true },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'roles', label: 'Role', sortable: false },
        { key: 'pages', label: 'Pages', sortable: false },
        { key: 'created_at', label: 'Created At', sortable: true },
    ];
    
    // Add Company column for super admin (after Name column)
    if (isSuperAdmin.value) {
        columns.splice(2, 0, { key: 'company.company_name', label: 'Company', sortable: true });
    }
    
    // Only add actions column if user has edit or delete permission
    if (canEditUser.value || canDeleteUser.value) {
        columns.push({ key: 'actions', label: 'Actions', sortable: false });
    }
    
    return columns;
});

const userForm = ref({
    user_id: '',
    name: '',
    password: '',
    role: '',
    company_id: '',
    company_name: '',
    pages: [],
});

const errors = ref({
    user_id: '',
    name: '',
    password: '',
    role: '',
    company_id: '',
    pages: '',
});

const isSuperAdmin = computed(() => currentUserRole.value === 'super admin');
const isAdmin = computed(() => currentUserRole.value === 'admin' && !isSuperAdmin.value);

const filteredCompanies = computed(() => {
    if (!companySearchQuery.value) {
        return availableCompanies.value;
    }
    const query = companySearchQuery.value.toLowerCase();
    return availableCompanies.value.filter(company =>
        company.company_name.toLowerCase().includes(query)
    );
});

const loadUsers = async () => {
    try {
        const response = await axios.get('/users');
        users.value = response.data.data || response.data;
        console.log('Loaded users with pages:', users.value.map(u => ({ id: u.id, name: u.name, pages: u.pages })));
    } catch (error) {
        console.error('Error loading users:', error);
    }
};

const loadRoles = async (companyId = null) => {
    try {
        let url = '/roles';
        // If super admin and company_id is provided, filter by company
        if (isSuperAdmin.value && companyId) {
            url += `?company_id=${companyId}`;
        }
        const response = await axios.get(url);
        const allRoles = response.data.roles || [];
        // Filter out "super admin" role from the dropdown
        let filteredRoles = allRoles.filter(role => role.name !== 'super admin');
        
        // Only super admin can see and assign admin role
        // All other users (admin and non-admin) cannot see admin role
        if (!isSuperAdmin.value) {
            filteredRoles = filteredRoles.filter(role => role.name.toLowerCase() !== 'admin');
        }
        
        // Users (except super admin and admin) cannot create users with the same role as themselves
        // Filter out the current user's role from the dropdown
        if (!isSuperAdmin.value && !isAdmin.value && currentUserRole.value) {
            const userRoleName = currentUserRole.value.toLowerCase().trim();
            filteredRoles = filteredRoles.filter(role => role.name.toLowerCase() !== userRoleName);
        }
        
        availableRoles.value = filteredRoles;
    } catch (error) {
        console.error('Error loading roles:', error);
    }
};

const loadPages = async (companyId = null) => {
    try {
        let url = '/pages';
        // If super admin and company_id is provided, filter by company
        // If not super admin, use logged-in user's company_id
        if (isSuperAdmin.value && companyId) {
            url += `?company_id=${companyId}`;
        } else if (!isSuperAdmin.value && currentUserCompanyId.value) {
            url += `?company_id=${currentUserCompanyId.value}`;
        }
        const response = await axios.get(url);
        availablePages.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Error loading pages:', error);
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
    userForm.value.company_id = company.id;
    userForm.value.company_name = company.company_name;
    companySearchQuery.value = company.company_name;
    showCompanyDropdown.value = false;
    // Reload roles and pages filtered by selected company (for super admin)
    if (isSuperAdmin.value) {
        await loadRoles(company.id);
        await loadPages(company.id);
        // Clear selected role and pages when company changes
        userForm.value.role = '';
        userForm.value.pages = [];
    }
};

const editUser = async (user) => {
    editingUser.value = user;
    userForm.value = {
        user_id: user.user_id || '',
        name: user.name,
        password: '',
        role: user.roles?.[0]?.name || '',
        company_id: user.company_id || '',
        company_name: user.company?.company_name || '',
        pages: user.pages || [],
    };
    if (isSuperAdmin.value && user.company) {
        companySearchQuery.value = user.company.company_name;
        // Load roles for the user's company
        await loadRoles(user.company_id);
    }
};

const clearErrors = () => {
    errors.value = {
        user_id: '',
        name: '',
        password: '',
        role: '',
        company_id: '',
    };
};

const saveUser = async () => {
    clearErrors();
    
    // Client-side validation
    if (!userForm.value.user_id) {
        errors.value.user_id = 'User ID is required';
    }
    if (!userForm.value.name) {
        errors.value.name = 'Name is required';
    }
    if (!editingUser.value && !userForm.value.password) {
        errors.value.password = 'Password is required';
    } else if (userForm.value.password && userForm.value.password.length < 8) {
        errors.value.password = 'Password must be at least 8 characters';
    }
    
    // If there are client-side errors, don't submit
    if (Object.values(errors.value).some(err => err !== '')) {
        if (window.toast) {
            window.toast.error('Please fix the validation errors');
        }
        return;
    }
    
    try {
        // Prepare form data with page IDs
        let pageIds = [];
        console.log('userForm.value.pages before processing:', userForm.value.pages);
        console.log('Type of pages:', typeof userForm.value.pages, 'Is array:', Array.isArray(userForm.value.pages));
        
        if (userForm.value.pages && Array.isArray(userForm.value.pages) && userForm.value.pages.length > 0) {
            pageIds = userForm.value.pages.map((page, index) => {
                console.log(`Processing page ${index}:`, page, 'Type:', typeof page);
                if (typeof page === 'object' && page !== null && page.id) {
                    return parseInt(page.id);
                } else if (typeof page === 'number') {
                    return parseInt(page);
                }
                console.warn('Invalid page format:', page);
                return null;
            }).filter(id => id !== null && !isNaN(id));
        }
        
        console.log('Extracted page IDs:', pageIds, 'Count:', pageIds.length);
        
        const formData = {
            user_id: userForm.value.user_id,
            name: userForm.value.name,
            password: userForm.value.password,
            role: userForm.value.role,
            pages: pageIds,
        };
        
        // Add company_id for super admin
        if (isSuperAdmin.value && userForm.value.company_id) {
            formData.company_id = userForm.value.company_id;
        }
        
        console.log('Final formData.pages:', formData.pages, 'Count:', formData.pages.length);
        
        if (editingUser.value) {
            await axios.put(`/users/${editingUser.value.id}`, formData);
            if (window.toast) {
                window.toast.success('User updated successfully');
            }
        } else {
            await axios.post('/users', formData);
            if (window.toast) {
                window.toast.success('User created successfully');
            }
        }
        await loadUsers();
        closeModal();
    } catch (error) {
        console.error('Error saving user:', error);
        
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
                window.toast.error(error.response?.data?.message || 'Error saving user');
            }
        }
    }
};

const showDeleteDialog = ref(false);
const userToDelete = ref(null);

const deleteUser = (userId) => {
    userToDelete.value = userId;
    showDeleteDialog.value = true;
};

const confirmDeleteUser = async () => {
    if (!userToDelete.value) return;
    
    try {
        await axios.delete(`/users/${userToDelete.value}`);
        await loadUsers();
        if (window.toast) {
            window.toast.success('User deleted successfully');
        }
        userToDelete.value = null;
    } catch (error) {
        console.error('Error deleting user:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error deleting user');
        }
        userToDelete.value = null;
    }
};

const removePage = (pageId) => {
    userForm.value.pages = userForm.value.pages.filter(page => page.id !== pageId);
};

const selectAllPages = () => {
    // Select all available pages
    userForm.value.pages = availablePages.value.map(page => ({
        id: page.id,
        page_id: page.page_id,
        page_name: page.page_name
    }));
};

const clearAllPages = () => {
    // Clear all selected pages
    userForm.value.pages = [];
};

const closeModal = async () => {
    showAddModal.value = false;
    editingUser.value = null;
    clearErrors();
    userForm.value = {
        user_id: '',
        name: '',
        password: '',
        role: '',
        company_id: '',
        company_name: '',
        pages: [],
    };
    companySearchQuery.value = '';
    showCompanyDropdown.value = false;
    // Reload all roles when modal is closed (for super admin, reset to show all roles)
    if (isSuperAdmin.value) {
        await loadRoles();
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

const openPagesDialog = (user) => {
    selectedUserPages.value = user.pages || [];
    selectedUserName.value = user.name || 'User';
    showPagesModal.value = true;
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

// Watch for role changes to auto-select all pages for admin
watch(() => userForm.value.role, async (newRole, oldRole) => {
    if (newRole === 'admin' && availablePages.value.length > 0) {
        // Auto-select all pages for admin - but only pages from the relevant company
        // For super admin: use selected company_id
        // For non-super admin: use currentUserCompanyId
        const relevantCompanyId = isSuperAdmin.value 
            ? userForm.value.company_id 
            : currentUserCompanyId.value;
        
        // Filter pages by company_id if we have one
        let pagesToSelect = availablePages.value;
        if (relevantCompanyId) {
            pagesToSelect = availablePages.value.filter(page => page.company_id === relevantCompanyId);
        }
        
        // Auto-select all pages for admin - create new array with all page objects
        userForm.value.pages = pagesToSelect.map(page => ({
            id: page.id,
            page_id: page.page_id,
            page_name: page.page_name
        }));
        console.log('Auto-selected all pages for admin role:', userForm.value.pages.length, 'pages', relevantCompanyId ? `(company_id: ${relevantCompanyId})` : '');
    } else if (newRole && newRole !== 'admin' && oldRole === 'admin') {
        // If changing from admin to another role, keep current pages (user can manually adjust)
        // Don't clear pages automatically
    }
});

// Watch for modal opening to reload roles with proper filtering
watch(showAddModal, async (isOpen) => {
    if (isOpen) {
        // Reload roles when modal opens to ensure admin role is filtered if needed
        const companyId = isSuperAdmin.value ? userForm.value.company_id : null;
        await loadRoles(companyId);
    }
});

// Watch for company_id changes to reload roles and pages (for super admin)
watch(() => userForm.value.company_id, async (newCompanyId, oldCompanyId) => {
    if (isSuperAdmin.value && newCompanyId !== oldCompanyId) {
        if (newCompanyId) {
            // Reload roles and pages filtered by selected company
            await loadRoles(newCompanyId);
            await loadPages(newCompanyId);
        } else {
            // If company is cleared, reload all roles
            await loadRoles();
            await loadPages();
        }
        // Clear selected role and pages when company changes
        userForm.value.role = '';
        userForm.value.pages = [];
        
        // If role is admin, auto-select all pages from the new company
        if (userForm.value.role === 'admin' && availablePages.value.length > 0 && newCompanyId) {
            const pagesToSelect = availablePages.value.filter(page => page.company_id === newCompanyId);
            userForm.value.pages = pagesToSelect.map(page => ({
                id: page.id,
                page_id: page.page_id,
                page_name: page.page_name
            }));
            console.log('Auto-selected all pages for admin role after company change:', userForm.value.pages.length, 'pages');
        }
    }
});

const handleClickOutside = (event) => {
    if (companyDropdownRef.value && !companyDropdownRef.value.contains(event.target)) {
        showCompanyDropdown.value = false;
    }
};

onMounted(async () => {
    await loadUserPermissions();
    await loadUsers();
    await loadRoles();
    // Wait a tick to ensure currentUserRole is set before checking isSuperAdmin
    await new Promise(resolve => setTimeout(resolve, 0));
    // Load pages - for non-super admin, filter by their company_id
    await loadPages();
    if (isSuperAdmin.value) {
        await loadCompanies();
    }
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

