<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-black px-4">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>
        
        <Card class="w-full max-w-md relative z-10 backdrop-blur-sm bg-white/10 border-white/20 shadow-2xl">
            <CardHeader class="space-y-4 text-center">
                <div class="flex justify-center mb-4">
                    <img 
                        src="/images/dravion-logo.png" 
                        alt="Dravion Enterprise Logo" 
                        class="h-24 w-auto object-contain drop-shadow-[0_0_15px_rgba(74,158,255,0.5)]"
                        @error="handleImageError"
                    />
                </div>
                <CardTitle class="text-2xl font-bold text-white">Welcome Back</CardTitle>
                <CardDescription class="text-gray-300">
                    Sign in to your account to continue
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div class="space-y-2">
                        <label for="user_id" class="text-xs font-medium text-gray-300">User ID</label>
                        <Input
                            id="user_id"
                            v-model="form.user_id"
                            type="text"
                            placeholder="Enter your User ID"
                            required
                            class="bg-white/10 border-white/20 text-white placeholder:text-gray-400 focus:border-blue-400 focus:ring-blue-400"
                        />
                        <p v-if="errors.user_id" class="text-xs text-red-400">{{ errors.user_id }}</p>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="password" class="text-xs font-medium text-gray-300">Password</label>
                        <div class="relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Enter your password"
                                required
                                class="bg-white/10 border-white/20 text-white placeholder:text-gray-400 focus:border-blue-400 focus:ring-blue-400 pr-10"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors"
                            >
                                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L12 12m-5.71-5.71L12 12" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password" class="text-xs text-red-400">{{ errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="w-4 h-4 rounded border-white/20 bg-white/10 text-blue-600 focus:ring-blue-500 focus:ring-offset-0"
                            />
                                <span class="text-xs text-gray-300">Remember me</span>
                            </label>
                            <a href="#" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                            Forgot password?
                        </a>
                    </div>

                    <Button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 rounded-md transition-all duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Signing in...
                        </span>
                        <span v-else>Sign In</span>
                    </Button>

                </form>
            </CardContent>
        </Card>
        
        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';
import Card from './ui/Card.vue';
import CardHeader from './ui/CardHeader.vue';
import CardTitle from './ui/CardTitle.vue';
import CardDescription from './ui/CardDescription.vue';
import CardContent from './ui/CardContent.vue';
import Input from './ui/Input.vue';
import Button from './ui/Button.vue';
import Toast from './ui/Toast.vue';

const form = reactive({
    user_id: '',
    password: '',
    remember: false,
});

const errors = reactive({
    user_id: '',
    password: '',
});

const loading = ref(false);
const showPassword = ref(false);
const errorMessage = ref('');

const handleImageError = (e) => {
    // If image fails to load, hide it or show placeholder
    e.target.style.display = 'none';
};

const getFirstAccessiblePage = (permissions, userRole) => {
    // Define page order and their required permissions
    // Note: Dashboard is included but will be skipped if user doesn't have 'access dashboard' permission
    const pageOrder = [
        { path: '/dashboard', permission: 'access dashboard' },
        { path: '/dashboard/users', permissions: ['view users', 'add user', 'edit user', 'delete user'] },
        { path: '/dashboard/roles', permission: 'view roles' },
        { path: '/dashboard/pages', permission: 'view pages' },
        { path: '/dashboard/payment-methods', permission: 'view payment methods' },
        { path: '/dashboard/deposit-form', permission: 'view deposit forms' },
        { path: '/dashboard/redeem-form', permission: 'view redeem forms' },
    ];
    
    // Check if user is super admin (can access companies)
    if (userRole === 'super admin') {
        pageOrder.splice(2, 0, { path: '/dashboard/companies', isSuperAdmin: true });
    }
    
    // Find the first accessible page
    for (const page of pageOrder) {
        if (page.isSuperAdmin && userRole === 'super admin') {
            return page.path;
        }
        
        if (page.permission && permissions.includes(page.permission)) {
            return page.path;
        }
        
        if (page.permissions && page.permissions.some(perm => permissions.includes(perm))) {
            return page.path;
        }
    }
    
    // If no accessible page found, return the first page in the order (users page as fallback)
    // This should rarely happen, but provides a safe fallback
    return '/dashboard/users';
};

const handleLogin = async () => {
    // Reset errors
    errors.user_id = '';
    errors.password = '';
    errorMessage.value = '';
    loading.value = true;

    try {
        const response = await axios.post('/login', {
            user_id: form.user_id,
            password: form.password,
            remember: form.remember,
        });

        if (response.data.success) {
            // Fetch user permissions to determine first accessible page
            try {
                const permissionsResponse = await axios.get('/user/permissions');
                const permissions = permissionsResponse.data.permissions || [];
                const userRole = permissionsResponse.data.user?.role || '';
                
                // Get the first accessible page based on permissions
                const redirectPath = getFirstAccessiblePage(permissions, userRole);
                console.log('🔍 Login redirect - User permissions:', permissions);
                console.log('🔍 Login redirect - User role:', userRole);
                console.log('🔍 Login redirect - Redirecting to:', redirectPath);
                
                // Redirect to the first accessible page
                window.location.href = redirectPath;
            } catch (permError) {
                // If permission fetch fails, fallback to dashboard
                console.error('Error fetching permissions:', permError);
                window.location.href = response.data.redirect || '/dashboard';
            }
        }
    } catch (error) {
        loading.value = false;
        
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors;
            if (validationErrors.user_id) {
                errors.user_id = validationErrors.user_id[0];
            }
            if (validationErrors.password) {
                errors.password = validationErrors.password[0];
            }
        } else {
            const errorMsg = error.response?.data?.message || 'Invalid credentials. Please try again.';
            errorMessage.value = errorMsg;
            if (window.toast) {
                window.toast.error(errorMsg);
            }
        }
    }
};
</script>

<style scoped>
@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>

