<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs sm:text-sm mb-1 sm:mb-2">
                    <a href="/dashboard" @click.prevent="goToDashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-900 font-semibold">Roles & Permissions</span>
                </div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mt-2 sm:mt-4">Roles & Permissions</h1>
            </div>
            <Button 
                v-if="canAddRole"
                @click="showAddModal = true" 
                class="bg-blue-600 hover:bg-blue-700 w-full sm:w-auto"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-xs sm:text-sm">Add New Role</span>
            </Button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Roles List -->
            <div class="lg:col-span-1 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">Roles</h2>
                        <div class="bg-white/20 rounded-full px-3 py-1">
                            <span class="text-xs font-semibold text-white">{{ roles.length }}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-3 max-h-[calc(100vh-300px)] overflow-y-auto">
                    <div v-for="role in roles" :key="role.id" 
                         class="group relative p-3 sm:p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer"
                         :class="selectedRole?.id === role.id 
                             ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-blue-100 shadow-md' 
                             : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 hover:shadow-sm'"
                         @click="selectRole(role)">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1 sm:mb-2">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md flex-shrink-0">
                                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-sm text-gray-900 capitalize">{{ role.name }}</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ role.permissions?.length || 0 }} permissions assigned</p>
                                        <p v-if="isSuperAdmin && role.company" class="text-xs text-gray-400 mt-0.5">{{ role.company.company_name }}</p>
                                    </div>
                                </div>
                            </div>
                            <button 
                                v-if="canDeleteRole"
                                @click.stop="deleteRole(role.id)" 
                                class="opacity-0 group-hover:opacity-100 transition-opacity p-2 rounded-lg hover:bg-red-50 text-red-600 hover:text-red-700"
                                title="Delete Role"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div v-if="roles.length === 0" class="text-center py-8 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <p class="text-sm">No roles found</p>
                    </div>
                </div>
            </div>

            <!-- Permissions for Selected Role -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-white p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                {{ selectedRole ? `Manage Permissions: ${selectedRole.name}` : 'Select a Role' }}
                            </h2>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ selectedRole ? `Assign permissions to the ${selectedRole.name} role` : 'Choose a role from the list to manage its permissions' }}
                            </p>
                        </div>
                        <div v-if="selectedRole" class="bg-blue-100 rounded-full px-4 py-2">
                            <span class="text-xs font-semibold text-blue-700">{{ selectedPermissions.length }} selected</span>
                        </div>
                    </div>
                </div>
                <div v-if="selectedRole" class="p-6">
                    <div class="space-y-6 max-h-[calc(100vh-400px)] overflow-y-auto pr-2">
                        <div v-if="permissions.length === 0" class="text-center py-12 text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <p class="text-xs">No permissions available. Run the seeder to create permissions.</p>
                        </div>
                        
                        <!-- Screen Permissions -->
                        <div v-if="screenPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-900">Screen Access</h3>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <label v-for="permission in screenPermissions" :key="permission.id" 
                                       class="flex items-center space-x-3 p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer group"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-blue-100 shadow-sm' 
                                           : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'">
                                    <div class="relative">
                                    <input
                                        type="checkbox"
                                        :checked="isPermissionAssigned(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        :disabled="!canManagePermissions && !isSuperAdmin"
                                        class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 focus:ring-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                    />
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-semibold text-gray-900">{{ formatPermissionName(permission.name) }}</span>
                                            <span v-if="isPermissionAssigned(permission.id)" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Active</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ getPermissionDescription(permission.name) }}</p>
                                    </div>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- User Management Permissions -->
                        <div v-if="userManagementPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-green-500 to-green-600 rounded-full"></div>
                                <h3 class="text-base font-bold text-gray-900">User Management</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label v-for="permission in userManagementPermissions" :key="permission.id" 
                                       class="flex items-center space-x-3 p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer group"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-green-500 bg-gradient-to-br from-green-50 to-green-100 shadow-sm' 
                                           : 'border-gray-200 hover:border-green-300 hover:bg-gray-50'">
                                    <div class="relative">
                                        <input
                                            type="checkbox"
                                            :checked="isPermissionAssigned(permission.id)"
                                            @change="togglePermission(permission.id)"
                                            class="w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-500 focus:ring-2 cursor-pointer"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-semibold text-gray-900">{{ formatPermissionName(permission.name) }}</span>
                                            <span v-if="isPermissionAssigned(permission.id)" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Active</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ getPermissionDescription(permission.name) }}</p>
                                    </div>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- Roles Management Permissions -->
                        <div v-if="rolesManagementPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-purple-500 to-purple-600 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-900">Roles & Permissions Management</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                <label v-for="permission in rolesManagementPermissions" :key="permission.id" 
                                       class="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-purple-500 bg-purple-50' 
                                           : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50'">
                                    <input
                                        type="checkbox"
                                        :checked="isPermissionAssigned(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        :disabled="!canManagePermissions && !isSuperAdmin"
                                        class="w-3 h-3 sm:w-4 sm:h-4 text-purple-600 rounded focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                                    />
                                    <span class="text-xs font-medium text-gray-700 flex-1 break-words">{{ formatPermissionName(permission.name) }}</span>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- Pages Management Permissions -->
                        <div v-if="pagesManagementPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-orange-500 to-orange-600 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-900">Pages Management</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                <label v-for="permission in pagesManagementPermissions" :key="permission.id" 
                                       class="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-orange-500 bg-orange-50' 
                                           : 'border-gray-200 hover:border-orange-300 hover:bg-gray-50'">
                                    <input
                                        type="checkbox"
                                        :checked="isPermissionAssigned(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        :disabled="!canManagePermissions && !isSuperAdmin"
                                        class="w-3 h-3 sm:w-4 sm:h-4 text-orange-600 rounded focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                                    />
                                    <span class="text-xs font-medium text-gray-700 flex-1 break-words">{{ formatPermissionName(permission.name) }}</span>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- Deposit Form Management Permissions -->
                        <div v-if="depositFormManagementPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-purple-500 to-purple-600 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-900">Deposit Form Management</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                <label v-for="permission in depositFormManagementPermissions" :key="permission.id" 
                                       class="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-purple-500 bg-purple-50' 
                                           : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50'">
                                    <input
                                        type="checkbox"
                                        :checked="isPermissionAssigned(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        :disabled="!canManagePermissions && !isSuperAdmin"
                                        class="w-3 h-3 sm:w-4 sm:h-4 text-purple-600 rounded focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                                    />
                                    <span class="text-xs font-medium text-gray-700 flex-1 break-words">{{ formatPermissionName(permission.name) }}</span>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- Redeem Form Management Permissions -->
                        <div v-if="redeemFormManagementPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-teal-500 to-teal-600 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-900">Redeem Form Management</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                <label v-for="permission in redeemFormManagementPermissions" :key="permission.id" 
                                       class="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-teal-500 bg-teal-50' 
                                           : 'border-gray-200 hover:border-teal-300 hover:bg-gray-50'">
                                    <input
                                        type="checkbox"
                                        :checked="isPermissionAssigned(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        :disabled="!canManagePermissions && !isSuperAdmin"
                                        class="w-3 h-3 sm:w-4 sm:h-4 text-teal-600 rounded focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                                    />
                                    <span class="text-xs font-medium text-gray-700 flex-1 break-words">{{ formatPermissionName(permission.name) }}</span>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-4 h-4 sm:w-5 sm:h-5 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- Redeem Process Management Permissions -->
                        <div v-if="redeemProcessManagementPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-purple-500 to-purple-600 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-900">Redeem Process Management</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                <label v-for="permission in redeemProcessManagementPermissions" :key="permission.id" 
                                       class="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-purple-500 bg-purple-50' 
                                           : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50'">
                                    <input
                                        type="checkbox"
                                        :checked="isPermissionAssigned(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        :disabled="!canManagePermissions && !isSuperAdmin"
                                        class="w-3 h-3 sm:w-4 sm:h-4 text-purple-600 rounded focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                                    />
                                    <span class="text-xs font-medium text-gray-700 flex-1 break-words">{{ formatPermissionName(permission.name) }}</span>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- Payment Method Management Permissions -->
                        <div v-if="paymentMethodManagementPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-pink-500 to-pink-600 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-900">Payment Method Management</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                <label v-for="permission in paymentMethodManagementPermissions" :key="permission.id" 
                                       class="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-pink-500 bg-pink-50' 
                                           : 'border-gray-200 hover:border-pink-300 hover:bg-gray-50'">
                                    <input
                                        type="checkbox"
                                        :checked="isPermissionAssigned(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        :disabled="!canManagePermissions && !isSuperAdmin"
                                        class="w-3 h-3 sm:w-4 sm:h-4 text-pink-600 rounded focus:ring-pink-500 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                                    />
                                    <span class="text-xs font-medium text-gray-700 flex-1 break-words">{{ formatPermissionName(permission.name) }}</span>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-4 h-4 sm:w-5 sm:h-5 text-pink-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- Company Management Permissions (Only visible to super admin) -->
                        <div v-if="isSuperAdmin && companyManagementPermissions.length > 0" class="space-y-3">
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-1 h-6 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded-full"></div>
                                <h3 class="text-sm font-bold text-gray-900">Company Management</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                <label v-for="permission in companyManagementPermissions" :key="permission.id" 
                                       class="flex items-center space-x-2 sm:space-x-3 p-2 sm:p-3 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                                       :class="isPermissionAssigned(permission.id) 
                                           ? 'border-indigo-500 bg-indigo-50' 
                                           : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'">
                                    <input
                                        type="checkbox"
                                        :checked="isPermissionAssigned(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        :disabled="!canManagePermissions && !isSuperAdmin"
                                        class="w-3 h-3 sm:w-4 sm:h-4 text-indigo-600 rounded focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                                    />
                                    <span class="text-xs font-medium text-gray-700 flex-1 break-words">{{ formatPermissionName(permission.name) }}</span>
                                    <svg v-if="isPermissionAssigned(permission.id)" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div v-if="canManagePermissions" class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
                        <Button 
                            @click="savePermissions" 
                            :disabled="!canManagePermissions && !isSuperAdmin"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-2 sm:py-3 rounded-xl shadow-lg transition-all duration-200 transform hover:scale-[1.02] text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Permissions
                        </Button>
                    </div>
                    <div v-else class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 sm:p-4 text-center">
                            <p class="text-xs sm:text-sm text-yellow-800">You do not have permission to manage permissions</p>
                        </div>
                    </div>
                </div>
                <div v-else class="p-6 sm:p-12 text-center">
                    <div class="w-16 h-16 sm:w-24 sm:h-24 mx-auto mb-3 sm:mb-4 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-semibold text-gray-700 mb-2">No Role Selected</h3>
                    <p class="text-xs sm:text-sm text-gray-500">Select a role from the left panel to manage its permissions</p>
                </div>
            </div>
        </div>

        <!-- Add Role Modal -->
        <div v-if="showAddModal" class="fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8 w-full max-w-md mx-auto border border-gray-200 transform transition-all max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between mb-4 sm:mb-6">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900">Create New Role</h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Add a new role to your system</p>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg flex-shrink-0 ml-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveRole" class="space-y-4 sm:space-y-6">
                    <div v-if="isSuperAdmin">
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Company</label>
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
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Role Name</label>
                        <Input 
                            v-model="roleForm.name" 
                            required 
                            placeholder="e.g., editor, manager, viewer" 
                            class="w-full"
                        />
                        <p class="text-xs text-gray-500 mt-2">Choose a descriptive name for this role</p>
                    </div>
                    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
                        <Button 
                            type="button" 
                            @click="closeModal" 
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-xl text-xs sm:text-sm"
                        >
                            Cancel
                        </Button>
                        <Button 
                            type="submit" 
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-lg text-xs sm:text-sm"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Role
                        </Button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Confirm Delete Dialog -->
        <ConfirmDialog
            v-model:isOpen="showDeleteDialog"
            title="Delete Role"
            message="Are you sure you want to delete this role? This action cannot be undone."
            @confirm="confirmDeleteRole"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Breadcrumb from '../ui/Breadcrumb.vue';
import BreadcrumbItem from '../ui/BreadcrumbItem.vue';
import BreadcrumbLink from '../ui/BreadcrumbLink.vue';
import BreadcrumbSeparator from '../ui/BreadcrumbSeparator.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import ConfirmDialog from '../ui/ConfirmDialog.vue';

const roles = ref([]);
const permissions = ref([]);
const selectedRole = ref(null);
const showAddModal = ref(false);
const selectedPermissions = ref([]);
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

const canAddRole = computed(() => hasPermission('add role'));
const canDeleteRole = computed(() => hasPermission('delete role'));
const canManagePermissions = computed(() => hasPermission('manage permissions'));

const screenPermissions = computed(() => {
    return permissions.value.filter(p => 
        p.name === 'access dashboard'
    );
});

const userManagementPermissions = computed(() => {
    const userPerms = ['view users', 'add user', 'edit user', 'delete user'];
    return permissions.value.filter(p => userPerms.includes(p.name));
});

const rolesManagementPermissions = computed(() => {
    const rolePerms = ['view roles', 'add role', 'edit role', 'delete role', 'manage permissions'];
    return permissions.value.filter(p => rolePerms.includes(p.name));
});

const pagesManagementPermissions = computed(() => {
    const pagePerms = ['view pages', 'add page', 'edit page', 'delete page'];
    return permissions.value.filter(p => pagePerms.includes(p.name));
});

const depositFormManagementPermissions = computed(() => {
    const depositPerms = ['view deposit forms', 'add deposit form', 'edit deposit form', 'delete deposit form'];
    return permissions.value.filter(p => depositPerms.includes(p.name));
});

const redeemFormManagementPermissions = computed(() => {
    const redeemPerms = ['view redeem forms', 'add redeem form', 'edit redeem form', 'delete redeem form'];
    return permissions.value.filter(p => redeemPerms.includes(p.name));
});

const redeemProcessManagementPermissions = computed(() => {
    const redeemProcessPerms = ['view redeem process'];
    return permissions.value.filter(p => redeemProcessPerms.includes(p.name));
});

const paymentMethodManagementPermissions = computed(() => {
    const paymentMethodPerms = ['view payment methods', 'add payment method', 'edit payment method', 'delete payment method'];
    return permissions.value.filter(p => paymentMethodPerms.includes(p.name));
});

const companyManagementPermissions = computed(() => {
    // Only show company permissions to super admin
    if (!isSuperAdmin.value) {
        return [];
    }
    const companyPerms = ['view companies', 'add company', 'edit company', 'delete company'];
    return permissions.value.filter(p => companyPerms.includes(p.name));
});

const roleForm = ref({
    name: '',
    company_id: null,
});

const errors = ref({
    name: '',
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

const loadRoles = async () => {
    try {
        const response = await axios.get('/roles');
        const allRoles = response.data.roles || [];
        // Filter out "super admin" role from the list
        let filteredRoles = allRoles.filter(role => role.name !== 'super admin');
        
        // Only super admin can see admin role, hide it from other users
        if (!isSuperAdmin.value) {
            filteredRoles = filteredRoles.filter(role => role.name.toLowerCase() !== 'admin');
        }
        
        roles.value = filteredRoles;
        const perms = response.data.permissions || [];
        permissions.value = Array.isArray(perms) ? perms : Object.values(perms).flat();
    } catch (error) {
        console.error('Error loading roles:', error);
        if (window.toast) {
            window.toast.error('Error loading roles and permissions');
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
    roleForm.value.company_id = company.id;
    companySearchQuery.value = company.company_name;
    showCompanyDropdown.value = false;
};

const selectRole = (role) => {
    selectedRole.value = role;
    selectedPermissions.value = role.permissions?.map(p => p.id) || [];
};

const isPermissionAssigned = (permissionId) => {
    return selectedPermissions.value.includes(permissionId);
};

const togglePermission = (permissionId) => {
    const index = selectedPermissions.value.indexOf(permissionId);
    if (index > -1) {
        selectedPermissions.value.splice(index, 1);
    } else {
        selectedPermissions.value.push(permissionId);
    }
};

const savePermissions = async () => {
    if (!selectedRole.value) return;
    
    // Super admin can assign permissions to any role
    // No restrictions for super admin
    
    try {
        await axios.put(`/roles/${selectedRole.value.id}`, {
            permissions: selectedPermissions.value,
        });
        await loadRoles();
        selectRole(roles.value.find(r => r.id === selectedRole.value.id));
        if (window.toast) {
            window.toast.success('Permissions saved successfully');
        }
    } catch (error) {
        console.error('Error saving permissions:', error);
        if (window.toast) {
            window.toast.error(error.response?.data?.message || 'Error saving permissions');
        }
    }
};

const saveRole = async () => {
    clearErrors();
    
    // Client-side validation
    if (!roleForm.value.name || !roleForm.value.name.trim()) {
        errors.value.name = 'Role name is required';
        if (window.toast) {
            window.toast.error('Please fix the validation errors');
        }
        return;
    }
    
    if (isSuperAdmin.value && !roleForm.value.company_id) {
        errors.value.company_id = 'Company is required';
        if (window.toast) {
            window.toast.error('Please fix the validation errors');
        }
        return;
    }
    
    try {
        await axios.post('/roles', roleForm.value);
        await loadRoles();
        closeModal();
        if (window.toast) {
            window.toast.success('Role created successfully');
        }
    } catch (error) {
        console.error('Error saving role:', error);
        
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
                window.toast.error(error.response?.data?.message || 'Error saving role');
            }
        }
    }
};

const clearErrors = () => {
    errors.value = {
        name: '',
        company_id: '',
    };
};

const showDeleteDialog = ref(false);
const roleToDelete = ref(null);

const deleteRole = (roleId) => {
    roleToDelete.value = roleId;
    showDeleteDialog.value = true;
};

const confirmDeleteRole = async () => {
    if (!roleToDelete.value) return;
    
    try {
        const response = await axios.delete(`/roles/${roleToDelete.value}`);
        await loadRoles();
        if (selectedRole.value?.id === roleToDelete.value) {
            selectedRole.value = null;
            selectedPermissions.value = [];
        }
        if (window.toast) {
            window.toast.success(response.data?.message || 'Role deleted successfully');
        }
        roleToDelete.value = null;
    } catch (error) {
        console.error('Error deleting role:', error);
        if (window.toast) {
            const errorMessage = error.response?.data?.message || 'Error deleting role';
            window.toast.error(errorMessage);
        }
        roleToDelete.value = null;
    }
};

const closeModal = () => {
    showAddModal.value = false;
    roleForm.value = {
        name: '',
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

const goToDashboard = () => {
    window.location.href = '/dashboard';
};

const formatPermissionName = (name) => {
    return name
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const getPermissionDescription = (name) => {
    const descriptions = {
        'access dashboard': 'Allows access to the main dashboard screen',
        'view users': 'Allows viewing the list of users',
        'add user': 'Allows creating new users',
        'edit user': 'Allows editing existing users',
        'delete user': 'Allows deleting users',
        'view roles': 'Allows viewing the list of roles',
        'add role': 'Allows creating new roles',
        'edit role': 'Allows editing existing roles',
        'delete role': 'Allows deleting roles',
        'manage permissions': 'Allows assigning and managing permissions for roles',
        'view pages': 'Allows viewing the list of pages',
        'add page': 'Allows creating new pages',
        'edit page': 'Allows editing existing pages',
        'delete page': 'Allows deleting pages',
        'view deposit forms': 'Allows viewing the list of deposit forms',
        'add deposit form': 'Allows creating new deposit forms',
        'edit deposit form': 'Allows editing existing deposit forms',
        'delete deposit form': 'Allows deleting deposit forms',
        'view redeem forms': 'Allows viewing the list of redeem forms',
        'add redeem form': 'Allows creating new redeem forms',
        'edit redeem form': 'Allows editing existing redeem forms',
        'delete redeem form': 'Allows deleting redeem forms',
        'view payment methods': 'Allows viewing the list of payment methods',
        'add payment method': 'Allows creating new payment methods',
        'edit payment method': 'Allows editing existing payment methods',
        'delete payment method': 'Allows deleting payment methods',
        'view companies': 'Allows viewing the list of companies',
        'add company': 'Allows creating new companies',
        'edit company': 'Allows editing existing companies',
        'delete company': 'Allows deleting companies',
    };
    return descriptions[name] || 'Permission';
};

const loadUserPermissions = async () => {
    try {
        const response = await axios.get('/user/permissions');
        userPermissions.value = response.data.permissions || [];
        currentUserRole.value = response.data.user?.role || '';
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
    await loadRoles();
    document.addEventListener('click', handleClickOutside);
});
</script>

