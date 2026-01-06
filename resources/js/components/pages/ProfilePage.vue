<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs sm:text-sm mb-1 sm:mb-2">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Profile</span>
                </div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mt-2 sm:mt-4">My Profile</h1>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Profile Card -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-white flex items-center justify-center text-blue-600 font-bold text-2xl sm:text-3xl shadow-lg">
                            {{ userInitials }}
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-white mb-1">{{ currentUser.name || 'User' }}</h2>
                            <p class="text-blue-100 text-sm sm:text-base">{{ currentUser.role || 'No Role' }}</p>
                            <p v-if="currentUser.company" class="text-blue-100 text-xs sm:text-sm mt-1">{{ currentUser.company.company_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Profile Form -->
                <div class="p-6 sm:p-8">
                    <form @submit.prevent="updateProfile" class="space-y-6">
                        <!-- User ID (Read-only) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">User ID</label>
                            <Input 
                                v-model="profileForm.user_id" 
                                readonly
                                class="bg-gray-50 cursor-not-allowed"
                            />
                            <p class="mt-1 text-xs text-gray-500">User ID cannot be changed</p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                            <Input 
                                v-model="profileForm.name" 
                                required
                                placeholder="Enter your full name"
                                :class="errors.name ? 'border-red-500 focus:ring-red-500' : ''"
                            />
                            <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
                        </div>

                        <!-- Current Password (for password change) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                            <div class="relative">
                                <Input 
                                    v-model="profileForm.current_password" 
                                    :type="showCurrentPassword ? 'text' : 'password'"
                                    placeholder="Enter current password to change password"
                                    :class="errors.current_password ? 'border-red-500 focus:ring-red-500' : ''"
                                />
                                <button
                                    type="button"
                                    @click="showCurrentPassword = !showCurrentPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg v-if="showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L12 12m-5.71-5.71L12 12" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Required only if you want to change your password</p>
                            <p v-if="errors.current_password" class="mt-1 text-xs text-red-600">{{ errors.current_password }}</p>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                            <div class="relative">
                                <Input 
                                    v-model="profileForm.new_password" 
                                    :type="showNewPassword ? 'text' : 'password'"
                                    placeholder="Enter new password (min 8 characters)"
                                    :class="errors.new_password ? 'border-red-500 focus:ring-red-500' : ''"
                                />
                                <button
                                    type="button"
                                    @click="showNewPassword = !showNewPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg v-if="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L12 12m-5.71-5.71L12 12" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Leave empty if you don't want to change password</p>
                            <p v-if="errors.new_password" class="mt-1 text-xs text-red-600">{{ errors.new_password }}</p>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                            <div class="relative">
                                <Input 
                                    v-model="profileForm.new_password_confirmation" 
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    placeholder="Confirm new password"
                                    :class="errors.new_password_confirmation ? 'border-red-500 focus:ring-red-500' : ''"
                                />
                                <button
                                    type="button"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg v-if="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L12 12m-5.71-5.71L12 12" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="errors.new_password_confirmation" class="mt-1 text-xs text-red-600">{{ errors.new_password_confirmation }}</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <Button
                                type="button"
                                @click="goToDashboard"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 sm:py-3 px-6 rounded-lg transition-all duration-200"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="loading"
                                class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-2 sm:py-3 px-6 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                            >
                                <span v-if="loading" class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Updating...
                                </span>
                                <span v-else class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Update Profile
                                </span>
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Input from '../ui/Input.vue';
import Button from '../ui/Button.vue';

const currentUser = ref({
    id: null,
    user_id: '',
    name: '',
    role: '',
    company: null,
});

const profileForm = ref({
    user_id: '',
    name: '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const errors = ref({
    name: '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const loading = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const userInitials = computed(() => {
    const name = currentUser.value.name || '';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] || '') + (parts[parts.length - 1][0] || '');
    }
    return name.substring(0, 2).toUpperCase() || 'U';
});

const loadUserProfile = async () => {
    try {
        const response = await axios.get('/user/permissions');
        const userData = response.data.user;
        currentUser.value = {
            id: userData.id,
            user_id: userData.user_id || '',
            name: userData.name || '',
            role: userData.role || '',
            company: null,
        };
        
        // Load company info if available
        if (userData.company_id) {
            try {
                // Try to get company from companies endpoint (for super admin) or use company_id
                if (userData.role === 'super admin') {
                    const companiesResponse = await axios.get('/user/companies');
                    const companies = companiesResponse.data.companies || [];
                    const company = companies.find(c => c.id === userData.company_id);
                    if (company) {
                        currentUser.value.company = company;
                    }
                }
            } catch (error) {
                console.error('Error loading company:', error);
            }
        }
        
        // Populate form with current user data
        profileForm.value = {
            user_id: currentUser.value.user_id,
            name: currentUser.value.name,
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        };
    } catch (error) {
        console.error('Error loading user profile:', error);
        if (window.toast) {
            window.toast.error('Error loading profile information');
        }
    }
};

const updateProfile = async () => {
    clearErrors();
    loading.value = true;

    // Validation
    if (!profileForm.value.name || !profileForm.value.name.trim()) {
        errors.value.name = 'Name is required';
        loading.value = false;
        if (window.toast) {
            window.toast.error('Please fix the validation errors');
        }
        return;
    }

    // If new password is provided, current password is required
    if (profileForm.value.new_password) {
        if (!profileForm.value.current_password) {
            errors.value.current_password = 'Current password is required to change password';
            loading.value = false;
            if (window.toast) {
                window.toast.error('Current password is required to change password');
            }
            return;
        }
        if (profileForm.value.new_password.length < 8) {
            errors.value.new_password = 'New password must be at least 8 characters';
            loading.value = false;
            if (window.toast) {
                window.toast.error('New password must be at least 8 characters');
            }
            return;
        }
        if (profileForm.value.new_password !== profileForm.value.new_password_confirmation) {
            errors.value.new_password_confirmation = 'Passwords do not match';
            loading.value = false;
            if (window.toast) {
                window.toast.error('Passwords do not match');
            }
            return;
        }
    }

    try {
        const payload = {
            name: profileForm.value.name.trim(),
        };

        // Only include password fields if new password is provided
        if (profileForm.value.new_password) {
            payload.current_password = profileForm.value.current_password;
            payload.password = profileForm.value.new_password;
            payload.password_confirmation = profileForm.value.new_password_confirmation;
        }

        await axios.put('/user/profile', payload);
        
        // Reload user profile
        await loadUserProfile();
        
        // Clear password fields
        profileForm.value.current_password = '';
        profileForm.value.new_password = '';
        profileForm.value.new_password_confirmation = '';
        
        if (window.toast) {
            window.toast.success('Profile updated successfully');
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors || {};
            if (validationErrors.name) {
                errors.value.name = validationErrors.name[0];
            }
            if (validationErrors.current_password) {
                errors.value.current_password = validationErrors.current_password[0];
            }
            if (validationErrors.password) {
                errors.value.new_password = validationErrors.password[0];
            }
            if (validationErrors.password_confirmation) {
                errors.value.new_password_confirmation = validationErrors.password_confirmation[0];
            }
        }
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error updating profile');
        }
    } finally {
        loading.value = false;
    }
};

const clearErrors = () => {
    errors.value = {
        name: '',
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
    };
};

const goToDashboard = () => {
    window.location.href = '/dashboard';
};

onMounted(async () => {
    await loadUserProfile();
});
</script>

