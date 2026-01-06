<template>
    <div class="min-h-screen bg-gray-50">
        <!-- App Bar -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
            <div class="flex items-center h-14 sm:h-16">
                <!-- Logo Container - Fixed width to match sidebar -->
                <div 
                    class="flex items-center flex-shrink-0 px-3 sm:px-4 lg:px-6 overflow-hidden transition-all duration-300 ease-in-out"
                    :style="{ width: sidebarOpen ? '288px' : '200px' }"
                >
                    <img 
                        src="/images/dravion-logo.png" 
                        alt="Dravion Enterprise" 
                        class="h-10 sm:h-14 w-auto object-contain"
                    />
                </div>
                
                <!-- Fixed Position Container for Divider and Toggle - Always at 288px (Desktop) -->
                <div class="hidden lg:flex items-center flex-shrink-0" style="position: absolute; left: 288px;">
                    <!-- Vertical Divider - Only show when sidebar is open -->
                    <div 
                        v-if="sidebarOpen" 
                        class="h-8 sm:h-10 w-px bg-gray-300 flex-shrink-0"
                    ></div>
                    
                    <!-- Toggle Button - Always visible at fixed position -->
                    <div class="flex items-center justify-center flex-shrink-0 px-2 sm:px-3">
                        <button
                            @click="toggleSidebar"
                            class="p-2 rounded-lg bg-white border border-gray-300 shadow-sm hover:bg-gray-50 hover:shadow-md transition-all duration-200 flex items-center justify-center"
                            :title="sidebarOpen ? 'Hide Sidebar' : 'Show Sidebar'"
                        >
                            <svg v-if="sidebarOpen" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Mobile Toggle Button - Positioned after logo -->
                <div class="lg:hidden flex items-center justify-center flex-shrink-0 px-2 sm:px-3">
                    <button
                        @click="toggleSidebar"
                        class="p-2 rounded-lg bg-white border border-gray-300 shadow-sm hover:bg-gray-50 hover:shadow-md transition-all duration-200 flex items-center justify-center"
                        :title="sidebarOpen ? 'Hide Sidebar' : 'Show Sidebar'"
                    >
                        <svg v-if="sidebarOpen" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                
                <!-- Spacer -->
                <div class="flex-1"></div>
                
                <!-- Profile Dropdown - Fixed position on right (Desktop only) -->
                <div class="hidden lg:flex items-center flex-shrink-0 px-3 sm:px-4 lg:px-6">
                    <DropdownMenu position="bottom-right">
                        <template #trigger>
                            <button class="flex items-center space-x-2 sm:space-x-3 p-1.5 sm:p-2 rounded-lg hover:bg-gray-100 transition-all duration-200">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs sm:text-sm shadow-md flex-shrink-0">
                                    {{ userInitials }}
                                </div>
                                <div class="text-left flex-shrink-0">
                                    <p class="text-xs font-semibold text-gray-900 leading-tight">{{ capitalizedUserName }}</p>
                                    <p class="text-xs text-gray-500 leading-tight hidden sm:block">{{ currentUser.role || 'No Role' }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-600 hidden sm:block flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </template>
                        <DropdownMenuItem @click="handleProfile">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Profile</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleLogout" class="text-red-600 hover:bg-red-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Logout</span>
                        </DropdownMenuItem>
                    </DropdownMenu>
                </div>
            </div>
        </header>

        <div class="flex relative">
            <!-- Sidebar -->
            <aside
                :class="[
                    'bg-gradient-to-b from-white to-gray-50 border-r border-gray-200 transition-all duration-300 ease-in-out shadow-lg',
                    'absolute lg:fixed',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                    sidebarOpen ? 'lg:w-72' : 'lg:w-0',
                    'w-72 h-[calc(100vh-3.5rem)] sm:h-[calc(100vh-4rem)] z-30',
                    'overflow-y-auto flex flex-col',
                    'lg:top-14 lg:left-0'
                ]"
            >
                <nav class="p-2 sm:p-4 space-y-1 flex-1">
                    <a
                        v-if="canAccessDashboard"
                        href="/dashboard"
                        @click.prevent="navigate('/dashboard')"
                        class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-3 rounded-xl transition-all duration-200"
                        :class="currentRoute === '/dashboard' 
                            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50 transform scale-105' 
                            : 'hover:bg-gray-100 text-gray-700 hover:shadow-md hover:scale-105'"
                    >
                        <div :class="currentRoute === '/dashboard' ? 'bg-white/20 p-1.5 sm:p-2 rounded-lg' : 'p-1.5 sm:p-2 rounded-lg bg-gray-100 group-hover:bg-blue-100'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="currentRoute === '/dashboard' ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                    <span class="text-xs sm:text-sm" :class="currentRoute === '/dashboard' ? 'font-semibold' : 'font-medium'">Dashboard</span>
                </a>

                <!-- Companies Section -->
                <a
                    v-if="canViewCompanies"
                    href="/dashboard/companies"
                    @click.prevent="navigate('/dashboard/companies')"
                    class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-3 rounded-xl transition-all duration-200"
                    :class="currentRoute === '/dashboard/companies' 
                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50 transform scale-105' 
                        : 'hover:bg-gray-100 text-gray-700 hover:shadow-md hover:scale-105'"
                >
                    <div :class="currentRoute === '/dashboard/companies' ? 'bg-white/20 p-1.5 sm:p-2 rounded-lg' : 'p-1.5 sm:p-2 rounded-lg bg-gray-100 group-hover:bg-blue-100'">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="currentRoute === '/dashboard/companies' ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm" :class="currentRoute === '/dashboard/companies' ? 'font-semibold' : 'font-medium'">Companies</span>
                </a>
                <!-- Pages Section -->
                <a
                    v-if="canViewPages"
                    href="/dashboard/pages"
                    @click.prevent="navigate('/dashboard/pages')"
                    class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-3 rounded-xl transition-all duration-200"
                    :class="currentRoute === '/dashboard/pages' 
                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50 transform scale-105' 
                        : 'hover:bg-gray-100 text-gray-700 hover:shadow-md hover:scale-105'"
                >
                    <div :class="currentRoute === '/dashboard/pages' ? 'bg-white/20 p-1.5 sm:p-2 rounded-lg' : 'p-1.5 sm:p-2 rounded-lg bg-gray-100 group-hover:bg-blue-100'">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="currentRoute === '/dashboard/pages' ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm" :class="currentRoute === '/dashboard/pages' ? 'font-semibold' : 'font-medium'">Pages</span>
                </a>
                <!-- Payment Method Section -->
                <a
                    v-if="canViewPaymentMethods"
                    href="/dashboard/payment-methods"
                    @click.prevent="navigate('/dashboard/payment-methods')"
                    class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-3 rounded-xl transition-all duration-200"
                    :class="currentRoute === '/dashboard/payment-methods' 
                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50 transform scale-105' 
                        : 'hover:bg-gray-100 text-gray-700 hover:shadow-md hover:scale-105'"
                >
                    <div :class="currentRoute === '/dashboard/payment-methods' ? 'bg-white/20 p-1.5 sm:p-2 rounded-lg' : 'p-1.5 sm:p-2 rounded-lg bg-gray-100 group-hover:bg-blue-100'">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="currentRoute === '/dashboard/payment-methods' ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm" :class="currentRoute === '/dashboard/payment-methods' ? 'font-semibold' : 'font-medium'">Payment Method</span>
                </a>
                    <!-- Deposit Form -->
                    <a
                        v-if="userPermissions.length > 0 && canViewDepositForms"
                        href="/dashboard/deposit-form"
                        @click.prevent="navigate('/dashboard/deposit-form')"
                        class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-3 rounded-xl transition-all duration-200"
                        :class="currentRoute === '/dashboard/deposit-form' 
                            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50 transform scale-105' 
                            : 'hover:bg-gray-100 text-gray-700 hover:shadow-md hover:scale-105'"
                    >
                        <div :class="currentRoute === '/dashboard/deposit-form' ? 'bg-white/20 p-1.5 sm:p-2 rounded-lg' : 'p-1.5 sm:p-2 rounded-lg bg-gray-100 group-hover:bg-blue-100'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="currentRoute === '/dashboard/deposit-form' ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm" :class="currentRoute === '/dashboard/deposit-form' ? 'font-semibold' : 'font-medium'">Deposit Form</span>
                    </a>
                    <!-- Redeem Form -->
                    <a
                        v-if="userPermissions.length > 0 && canViewRedeemForms"
                        href="/dashboard/redeem-form"
                        @click.prevent="navigate('/dashboard/redeem-form')"
                        class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-3 rounded-xl transition-all duration-200"
                        :class="currentRoute === '/dashboard/redeem-form' 
                            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50 transform scale-105' 
                            : 'hover:bg-gray-100 text-gray-700 hover:shadow-md hover:scale-105'"
                    >
                        <div :class="currentRoute === '/dashboard/redeem-form' ? 'bg-white/20 p-1.5 sm:p-2 rounded-lg' : 'p-1.5 sm:p-2 rounded-lg bg-gray-100 group-hover:bg-blue-100'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="currentRoute === '/dashboard/redeem-form' ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm" :class="currentRoute === '/dashboard/redeem-form' ? 'font-semibold' : 'font-medium'">Redeem Form</span>
                    </a>
                    <!-- Redeem Process -->
                    <a
                        v-if="userPermissions.length > 0 && canViewRedeemProcess"
                        href="/dashboard/redeem-process"
                        @click.prevent="navigate('/dashboard/redeem-process')"
                        class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-3 rounded-xl transition-all duration-200"
                        :class="currentRoute === '/dashboard/redeem-process' 
                            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50 transform scale-105' 
                            : 'hover:bg-gray-100 text-gray-700 hover:shadow-md hover:scale-105'"
                    >
                        <div :class="currentRoute === '/dashboard/redeem-process' ? 'bg-white/20 p-1.5 sm:p-2 rounded-lg' : 'p-1.5 sm:p-2 rounded-lg bg-gray-100 group-hover:bg-blue-100'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="currentRoute === '/dashboard/redeem-process' ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm" :class="currentRoute === '/dashboard/redeem-process' ? 'font-semibold' : 'font-medium'">Redeem Process</span>
                    </a>

                    <!-- User Management Section -->
                    <div v-if="canViewUsers || canViewRoles" class="mt-2 sm:mt-4">
                        <button
                            @click="userManagementOpen = !userManagementOpen"
                            class="w-full flex items-center justify-between px-3 sm:px-4 py-2 sm:py-3 rounded-xl hover:bg-gray-100 transition-all duration-200 group"
                        >
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="p-1.5 sm:p-2 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition-colors">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs sm:text-sm font-medium text-gray-700">User Management</span>
                            </div>
                            <svg
                                class="w-3 h-3 sm:w-4 sm:h-4 text-gray-500 transition-transform duration-200"
                                :class="{ 'rotate-90': userManagementOpen }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 transform -translate-y-2"
                            enter-to-class="opacity-100 transform translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 transform translate-y-0"
                            leave-to-class="opacity-0 transform -translate-y-2"
                        >
                            <div v-show="userManagementOpen" class="ml-2 sm:ml-4 mt-2 space-y-1 border-l-2 border-gray-200 pl-2 sm:pl-4">
                                <a
                                    v-if="canViewUsers"
                                    href="/dashboard/users"
                                    @click.prevent="(e) => { e.preventDefault(); e.stopPropagation(); console.log('🔵 Users link clicked, calling navigate'); navigate('/dashboard/users').catch(err => { console.error('Navigation error:', err); }); }"
                                    class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200"
                                    :class="currentRoute === '/dashboard/users'
                                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md shadow-blue-500/30'
                                        : 'hover:bg-gray-100 text-gray-700 hover:shadow-sm'"
                                >
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" :class="currentRoute === '/dashboard/users' ? 'text-white' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="text-xs" :class="currentRoute === '/dashboard/users' ? 'font-semibold' : 'font-medium'">Users</span>
                                </a>
                                <a
                                    v-if="canViewRoles"
                                    href="/dashboard/roles"
                                    @click.prevent="navigate('/dashboard/roles')"
                                    class="group flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200"
                                    :class="currentRoute === '/dashboard/roles'
                                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md shadow-blue-500/30'
                                        : 'hover:bg-gray-100 text-gray-700 hover:shadow-sm'"
                                >
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" :class="currentRoute === '/dashboard/roles' ? 'text-white' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span class="text-xs" :class="currentRoute === '/dashboard/roles' ? 'font-semibold' : 'font-medium'">Roles & Permissions</span>
                                </a>
                            </div>
                        </transition>
                    </div>
                </nav>
                
                <!-- Mobile Profile Dropdown - Bottom of Sidebar (Mobile only) -->
                <div class="lg:hidden border-t border-gray-200 p-4 mt-auto">
                    <DropdownMenu position="top-left">
                        <template #trigger>
                            <button class="w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-all duration-200">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shadow-md flex-shrink-0">
                                    {{ userInitials }}
                                </div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-900 leading-tight truncate">{{ capitalizedUserName }}</p>
                                    <p class="text-xs text-gray-500 leading-tight truncate">{{ currentUser.role || 'No Role' }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </template>
                        <DropdownMenuItem @click="handleProfile">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Profile</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleLogout" class="text-red-600 hover:bg-red-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Logout</span>
                        </DropdownMenuItem>
                    </DropdownMenu>
                </div>
            </aside>

            <!-- Sidebar Overlay for mobile -->
            <div
                v-if="sidebarOpen"
                @click="sidebarOpen = false"
                class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
            ></div>

            <!-- Main Content -->
            <main 
                :class="[
                    'p-4 sm:p-6 transition-all duration-300 ease-in-out overflow-x-hidden',
                    sidebarOpen ? 'lg:ml-72' : 'lg:ml-0',
                    'w-full lg:flex-1'
                ]"
            >
                <div v-if="!currentComponent" class="text-center py-12">
                    <p class="text-gray-500">Loading...</p>
                    <p v-if="userPermissions.length > 0" class="text-red-500 mt-2 text-sm">You do not have permission to access this page.</p>
                </div>
                <component v-else :is="currentComponent" :key="currentRoute" />
            </main>
        </div>
        
        <!-- Toast Notifications -->
        <Toast />
        
        <!-- Confirm Logout Dialog -->
        <ConfirmDialog
            v-model:isOpen="showLogoutDialog"
            title="Logout"
            message="Are you sure you want to logout? You will need to login again to access the system."
            @confirm="confirmLogout"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';
import DashboardPage from './pages/DashboardPage.vue';
import UsersPage from './pages/UsersPage.vue';
import RolesPage from './pages/RolesPage.vue';
import PagesPage from './pages/PagesPage.vue';
import PaymentMethodPage from './pages/PaymentMethodPage.vue';
import CompanyPage from './pages/CompanyPage.vue';
import DepositFormPage from './pages/DepositFormPage.vue';
import RedeemFormPage from './pages/RedeemFormPage.vue';
import RedeemProcessPage from './pages/RedeemProcessPage.vue';
import ProfilePage from './pages/ProfilePage.vue';
import DropdownMenu from './ui/DropdownMenu.vue';
import DropdownMenuItem from './ui/DropdownMenuItem.vue';
import Toast from './ui/Toast.vue';
import ConfirmDialog from './ui/ConfirmDialog.vue';

// Normalize route path (remove trailing slash, but keep root as /) - must be defined before use
const normalizeRoute = (path) => {
    if (path === '/') return '/';
    return path.replace(/\/+$/, '') || '/';
};

const sidebarOpen = ref(false);
const userManagementOpen = ref(true);
const currentRoute = ref(normalizeRoute(window.location.pathname));
const userPermissions = ref([]);
const showLogoutDialog = ref(false);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const currentUser = ref({
    name: '',
    email: '',
    role: '',
});

// Compute user initials from name
const userInitials = computed(() => {
    if (!currentUser.value.name) return 'U';
    const names = currentUser.value.name.trim().split(/\s+/);
    if (names.length >= 2) {
        return (names[0][0] + names[names.length - 1][0]).toUpperCase();
    }
    return names[0][0].toUpperCase();
});

// Capitalize first letter of user name
const capitalizedUserName = computed(() => {
    if (!currentUser.value.name) return 'User';
    const name = currentUser.value.name.trim();
    return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
});

const checkScreenSize = () => {
    windowWidth.value = window.innerWidth;
    if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
    } else {
        sidebarOpen.value = true;
    }
};

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

const canAccessDashboard = computed(() => hasPermission('access dashboard'));
// Allow access to users page if user has ANY user-related permission
const canViewUsers = computed(() => {
    return hasPermission('view users') || 
           hasPermission('add user') || 
           hasPermission('edit user') || 
           hasPermission('delete user');
});
const canViewRoles = computed(() => hasPermission('view roles'));
const canViewPages = computed(() => hasPermission('view pages'));
const canViewPaymentMethods = computed(() => hasPermission('view payment methods'));
const canViewCompanies = computed(() => {
    // Only super admin can view companies
    const userRole = currentUser.value?.role || '';
    return userRole === 'super admin';
});
const canViewDepositForms = computed(() => {
    const perm = 'view deposit forms';
    const hasPerm = userPermissions.value.includes(perm);
    if (userPermissions.value.length > 0) {
        console.log('🔍 canViewDepositForms check - permission:', perm, 'hasPermission:', hasPerm, 'all permissions:', userPermissions.value);
    }
    return hasPerm;
});
const canViewRedeemForms = computed(() => {
    const perm = 'view redeem forms';
    const hasPerm = userPermissions.value.includes(perm);
    if (userPermissions.value.length > 0) {
        console.log('🔍 canViewRedeemForms check - permission:', perm, 'hasPermission:', hasPerm, 'all permissions:', userPermissions.value);
    }
    return hasPerm;
});
const canViewRedeemProcess = computed(() => {
    const perm = 'view redeem process';
    const hasPerm = userPermissions.value.includes(perm);
    if (userPermissions.value.length > 0) {
        console.log('🔍 canViewRedeemProcess check - permission:', perm, 'hasPermission:', hasPerm, 'all permissions:', userPermissions.value);
    }
    return hasPerm;
});

const routes = {
    '/dashboard': DashboardPage,
    '/dashboard/users': UsersPage,
    '/dashboard/roles': RolesPage,
    '/dashboard/pages': PagesPage,
    '/dashboard/payment-methods': PaymentMethodPage,
    '/dashboard/companies': CompanyPage,
    '/dashboard/deposit-form': DepositFormPage,
    '/dashboard/redeem-form': RedeemFormPage,
    '/dashboard/redeem-process': RedeemProcessPage,
    '/dashboard/profile': ProfilePage,
};

const currentComponent = computed(() => {
    // Normalize the route for component lookup
    const normalizedRoute = normalizeRoute(currentRoute.value);
    
    // Check permissions before returning component
    if (normalizedRoute === '/dashboard/deposit-form' && !canViewDepositForms.value) {
        console.log('🚫 Blocking deposit form - user does not have permission');
        // Return null or redirect to a safe page
        return null;
    }
    
    if (normalizedRoute === '/dashboard/redeem-form' && !canViewRedeemForms.value) {
        console.log('🚫 Blocking redeem form - user does not have permission');
        // Return null or redirect to a safe page
        return null;
    }
    
    if (normalizedRoute === '/dashboard/redeem-process' && !canViewRedeemProcess.value) {
        console.log('🚫 Blocking redeem process - user does not have permission');
        // Return null or redirect to a safe page
        return null;
    }
    
    if (normalizedRoute === '/dashboard/users' && !canViewUsers.value) {
        console.log('🚫 Blocking users page - user does not have permission');
        return null;
    }
    
    if (normalizedRoute === '/dashboard/roles' && !canViewRoles.value) {
        console.log('🚫 Blocking roles page - user does not have permission');
        return null;
    }
    
    if (normalizedRoute === '/dashboard/pages' && !canViewPages.value) {
        console.log('🚫 Blocking pages page - user does not have permission');
        return null;
    }
    
    if (normalizedRoute === '/dashboard/payment-methods' && !canViewPaymentMethods.value) {
        console.log('🚫 Blocking payment methods page - user does not have permission');
        return null;
    }
    
    if (normalizedRoute === '/dashboard/companies' && !canViewCompanies.value) {
        console.log('🚫 Blocking companies page - user does not have permission');
        return null;
    }
    
    if (normalizedRoute === '/dashboard' && !canAccessDashboard.value) {
        console.log('🚫 Blocking dashboard - user does not have permission');
        return null;
    }
    
    const component = routes[normalizedRoute] || DashboardPage;
    // Only log if route changes to avoid spam
    if (normalizedRoute !== '/dashboard' || currentRoute.value !== '/dashboard') {
        console.log('Component lookup - Current route:', currentRoute.value, 'Normalized:', normalizedRoute, 'Component:', component?.name || component);
    }
    return component;
});

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const navigate = async (path) => {
    try {
        console.log('🚀 Navigating to:', path);
        
        // Normalize the path
        const normalizedPath = normalizeRoute(path);
        console.log('📍 Normalized path:', normalizedPath);
        
        // Ensure permissions are loaded
        if (userPermissions.value.length === 0) {
            console.log('⏳ Permissions not loaded, loading now...');
            await loadUserPermissions();
            console.log('✅ Permissions loaded:', userPermissions.value);
        }
        
        // Force recompute canViewUsers
        const hasViewUsers = hasPermission('view users');
        const hasAddUser = hasPermission('add user');
        const hasEditUser = hasPermission('edit user');
        const hasDeleteUser = hasPermission('delete user');
        const canAccess = hasViewUsers || hasAddUser || hasEditUser || hasDeleteUser;
        
        console.log('🔍 Permission check - canViewUsers computed:', canViewUsers.value);
        console.log('🔍 Permission check - hasViewUsers:', hasViewUsers, 'hasAddUser:', hasAddUser, 'hasEditUser:', hasEditUser, 'hasDeleteUser:', hasDeleteUser);
        console.log('🔍 Permission check - canAccess (manual):', canAccess);
    
    // Check permissions before navigating - only block if user doesn't have permission for the specific page
    if (normalizedPath === '/dashboard' && !canAccessDashboard.value) {
        alert('You do not have permission to access the dashboard.');
        // Redirect to first available page or show error
        if (canViewUsers.value) {
            currentRoute.value = '/dashboard/users';
            window.history.pushState({}, '', '/dashboard/users');
        } else if (canViewRoles.value) {
            currentRoute.value = '/dashboard/roles';
            window.history.pushState({}, '', '/dashboard/roles');
        } else {
            alert('You do not have access to any pages. Please contact your administrator.');
            return;
        }
        return;
    }
    
    // Check permission for users page
    if (normalizedPath === '/dashboard/users') {
        console.log('👤 Checking users page access - canViewUsers:', canViewUsers.value);
        console.log('👤 User permissions:', userPermissions.value);
        console.log('👤 Has view users:', hasPermission('view users'));
        console.log('👤 Has add user:', hasPermission('add user'));
        console.log('👤 Has edit user:', hasPermission('edit user'));
        console.log('👤 Has delete user:', hasPermission('delete user'));
        
        // Use manual check as fallback
        const manualCheck = hasViewUsers || hasAddUser || hasEditUser || hasDeleteUser;
        console.log('👤 Manual permission check result:', manualCheck);
        
        if (!canViewUsers.value && !manualCheck) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            } else {
                alert('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            }
            return;
        }
        // User has permission, allow navigation
        console.log('Allowing navigation to /dashboard/users - user has user-related permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // Check permission for roles page
    if (normalizedPath === '/dashboard/roles') {
        if (!canViewRoles.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            }
            return;
        }
        // User has permission, allow navigation
        console.log('Allowing navigation to /dashboard/roles - user has view roles permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // Check permission for pages page
    if (normalizedPath === '/dashboard/pages') {
        if (!canViewPages.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPaymentMethods.value) {
                currentRoute.value = '/dashboard/payment-methods';
                window.history.pushState({}, '', '/dashboard/payment-methods');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            }
            return;
        }
        // User has permission, allow navigation
        console.log('Allowing navigation to /dashboard/pages - user has view pages permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }

    // Check permission for payment methods page
    if (normalizedPath === '/dashboard/payment-methods') {
        if (!canViewPaymentMethods.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else if (canViewCompanies.value) {
                currentRoute.value = '/dashboard/companies';
                window.history.pushState({}, '', '/dashboard/companies');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            }
            return;
        }
        // User has permission, allow navigation
        console.log('Allowing navigation to /dashboard/payment-methods - user has view payment methods permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }

    // Check permission for companies page
    if (normalizedPath === '/dashboard/companies') {
        if (!canViewCompanies.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else if (canViewPaymentMethods.value) {
                currentRoute.value = '/dashboard/payment-methods';
                window.history.pushState({}, '', '/dashboard/payment-methods');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            }
            return;
        }
        // User has permission, allow navigation
        console.log('Allowing navigation to /dashboard/companies - user has view companies permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }

    // Check permission for deposit form page
    if (normalizedPath === '/dashboard/deposit-form') {
        if (!canViewDepositForms.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else if (canViewRedeemForms.value) {
                currentRoute.value = '/dashboard/redeem-form';
                window.history.pushState({}, '', '/dashboard/redeem-form');
            }
            return;
        }
        // User has permission, allow navigation
        console.log('Allowing navigation to /dashboard/deposit-form - user has view deposit forms permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // Check permission for redeem form page
    if (normalizedPath === '/dashboard/redeem-form') {
        if (!canViewRedeemForms.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            }
            return;
        }
        // User has permission, allow navigation
        console.log('Allowing navigation to /dashboard/redeem-form - user has view redeem forms permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // Check permission for redeem process page
    if (normalizedPath === '/dashboard/redeem-process') {
        if (!canViewRedeemProcess.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            } else if (canViewRedeemForms.value) {
                currentRoute.value = '/dashboard/redeem-form';
                window.history.pushState({}, '', '/dashboard/redeem-form');
            }
            return;
        }
        // User has permission, allow navigation
        console.log('Allowing navigation to /dashboard/redeem-process - user has view redeem process permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // Profile page - accessible to all authenticated users
    if (normalizedPath === '/dashboard/profile') {
        console.log('Allowing navigation to /dashboard/profile - accessible to all authenticated users');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // For dashboard route (if we get here, user has permission)
    if (normalizedPath === '/dashboard') {
        console.log('Allowing navigation to /dashboard - user has access dashboard permission');
        currentRoute.value = normalizedPath;
        window.history.pushState({}, '', normalizedPath);
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
    }
    } catch (error) {
        console.error('❌ Error in navigate function:', error);
        console.error('Error stack:', error.stack);
        // Prevent page reload by catching the error
        if (window.toast) {
            window.toast.error('An error occurred while navigating. Please try again.');
        } else {
            alert('An error occurred while navigating. Please try again.');
        }
    }
};

const loadUserPermissions = async () => {
    try {
        const response = await axios.get('/user/permissions');
        userPermissions.value = response.data.permissions || [];
        
        // Update current user info
        if (response.data.user) {
            currentUser.value = {
                name: response.data.user.name || '',
                email: response.data.user.email || '',
                role: response.data.user.role || 'No Role',
            };
        }
        
        console.log('✅ Permissions loaded:', userPermissions.value);
        console.log('✅ Can access dashboard:', canAccessDashboard.value);
        console.log('✅ Can view users:', canViewUsers.value);
        console.log('✅ Can view roles:', canViewRoles.value);
        console.log('✅ Can view pages:', canViewPages.value);
        console.log('✅ Can view payment methods:', canViewPaymentMethods.value);
        console.log('✅ Can view deposit forms:', canViewDepositForms.value, 'Has permission:', hasPermission('view deposit forms'));
        console.log('✅ Can view redeem forms:', canViewRedeemForms.value, 'Has permission:', hasPermission('view redeem forms'));
        console.log('✅ Can view redeem process:', canViewRedeemProcess.value, 'Has permission:', hasPermission('view redeem process'));
        console.log('✅ Can view companies:', canViewCompanies.value);
    } catch (error) {
        console.error('Error loading permissions:', error);
        // Redirect to login if unauthorized
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
    }
};

const handleRouteChange = async () => {
    // Normalize the route path (remove trailing slash)
    const route = normalizeRoute(window.location.pathname);
    
    // Ensure permissions are loaded
    if (userPermissions.value.length === 0) {
        await loadUserPermissions();
    }
    
    // Check permissions for the route - only block if user doesn't have permission for the specific page
    if (route === '/dashboard' && !canAccessDashboard.value) {
        if (window.toast) {
            window.toast.error('You do not have permission to access the dashboard.');
        }
        // Redirect to first available page
        if (canViewUsers.value) {
            currentRoute.value = '/dashboard/users';
            window.history.pushState({}, '', '/dashboard/users');
        } else if (canViewRoles.value) {
            currentRoute.value = '/dashboard/roles';
            window.history.pushState({}, '', '/dashboard/roles');
        } else {
            if (window.toast) {
                window.toast.error('You do not have access to any pages. Please contact your administrator.');
            }
            return;
        }
        return;
    }
    
    // Check permission for users page
    if (route === '/dashboard/users') {
        console.log('handleRouteChange - Checking users page access - canViewUsers:', canViewUsers.value);
        console.log('handleRouteChange - User permissions:', userPermissions.value);
        console.log('handleRouteChange - Has view users:', hasPermission('view users'));
        console.log('handleRouteChange - Has add user:', hasPermission('add user'));
        console.log('handleRouteChange - Has edit user:', hasPermission('edit user'));
        console.log('handleRouteChange - Has delete user:', hasPermission('delete user'));
        
        if (!canViewUsers.value) {
            console.log('handleRouteChange - User does NOT have permission, redirecting...');
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            }
            return;
        }
        // User has permission, allow access
        console.log('handleRouteChange - Allowing access to /dashboard/users');
        currentRoute.value = route;
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // Check permission for roles page
    if (route === '/dashboard/roles') {
        if (!canViewRoles.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            }
            return;
        }
        // User has permission, allow access
        currentRoute.value = route;
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // Check permission for pages page
    if (route === '/dashboard/pages') {
        if (!canViewPages.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            }
            return;
        }
        // User has permission, allow access
        currentRoute.value = route;
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // Profile page - accessible to all authenticated users
    if (route === '/dashboard/profile') {
        console.log('handleRouteChange - Allowing access to /dashboard/profile - accessible to all authenticated users');
        currentRoute.value = route;
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
        return;
    }
    
    // For dashboard route (if we get here, user has permission)
    if (route === '/dashboard') {
        currentRoute.value = route;
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
    }
};

const handleProfile = () => {
    navigate('/dashboard/profile');
};

const handleLogout = () => {
    showLogoutDialog.value = true;
};

const confirmLogout = async () => {
    try {
        await axios.post('/logout');
        window.location.href = '/login';
    } catch (error) {
        console.error('Logout error:', error);
        window.location.href = '/login';
    }
};

onMounted(async () => {
    // Load permissions FIRST before setting route
    await loadUserPermissions();
    
    // Normalize and set initial route AFTER permissions are loaded
    const initialRoute = normalizeRoute(window.location.pathname);
    
    // Check screen size and set sidebar state
    checkScreenSize();
    
    // Listen for window resize events
    window.addEventListener('resize', checkScreenSize);
    
    // Check if user has permission for current route after permissions are loaded
    // Only block if user doesn't have permission for the specific page they're trying to access
    if (initialRoute === '/dashboard' && !canAccessDashboard.value) {
        if (window.toast) {
            window.toast.error('You do not have permission to access the dashboard.');
        }
        // Redirect to first available page (in priority order)
        if (canViewUsers.value) {
            currentRoute.value = '/dashboard/users';
            window.history.pushState({}, '', '/dashboard/users');
        } else if (canViewRoles.value) {
            currentRoute.value = '/dashboard/roles';
            window.history.pushState({}, '', '/dashboard/roles');
        } else if (canViewPages.value) {
            currentRoute.value = '/dashboard/pages';
            window.history.pushState({}, '', '/dashboard/pages');
        } else if (canViewPaymentMethods.value) {
            currentRoute.value = '/dashboard/payment-methods';
            window.history.pushState({}, '', '/dashboard/payment-methods');
        } else if (canViewDepositForms.value) {
            currentRoute.value = '/dashboard/deposit-form';
            window.history.pushState({}, '', '/dashboard/deposit-form');
        } else if (canViewRedeemForms.value) {
            currentRoute.value = '/dashboard/redeem-form';
            window.history.pushState({}, '', '/dashboard/redeem-form');
        } else if (canViewRedeemProcess.value) {
            currentRoute.value = '/dashboard/redeem-process';
            window.history.pushState({}, '', '/dashboard/redeem-process');
        } else if (canViewCompanies.value) {
            currentRoute.value = '/dashboard/companies';
            window.history.pushState({}, '', '/dashboard/companies');
        } else {
            if (window.toast) {
                window.toast.error('You do not have access to any pages. Please contact your administrator.');
            }
            // Even if no pages accessible, set a route to prevent errors
            currentRoute.value = '/dashboard';
        }
    } else if (initialRoute === '/dashboard/users') {
        // Check permission for users page
        console.log('Initial route is /dashboard/users, canViewUsers:', canViewUsers.value, 'permissions:', userPermissions.value);
        console.log('Has view users:', hasPermission('view users'));
        console.log('Has add user:', hasPermission('add user'));
        console.log('Has edit user:', hasPermission('edit user'));
        console.log('Has delete user:', hasPermission('delete user'));
        
        if (!canViewUsers.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            }
        } else {
            // User has permission, allow access
            console.log('Allowing initial access to /dashboard/users');
            currentRoute.value = initialRoute;
        }
    } else if (initialRoute === '/dashboard/roles') {
        // Check permission for roles page
        console.log('Initial route is /dashboard/roles, canViewRoles:', canViewRoles.value, 'permissions:', userPermissions.value);
        if (!canViewRoles.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            }
        } else {
            // User has permission, allow access
            console.log('Allowing initial access to /dashboard/roles');
            currentRoute.value = initialRoute;
        }
    } else if (initialRoute === '/dashboard/pages') {
        // Check permission for pages page
        console.log('Initial route is /dashboard/pages, canViewPages:', canViewPages.value, 'permissions:', userPermissions.value);
        if (!canViewPages.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            }
        } else {
            // User has permission, allow access
            console.log('Allowing initial access to /dashboard/pages');
            currentRoute.value = initialRoute;
        }
    } else if (initialRoute === '/dashboard/deposit-form') {
        console.log('Initial route is /dashboard/deposit-form, canViewDepositForms:', canViewDepositForms.value, 'permissions:', userPermissions.value);
        if (!canViewDepositForms.value) {
            console.log('🚫 User does not have permission for deposit form, redirecting...');
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page - prioritize redeem form if user has that permission
            if (canViewRedeemForms.value) {
                currentRoute.value = '/dashboard/redeem-form';
                window.history.pushState({}, '', '/dashboard/redeem-form');
            } else if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else {
                // No accessible pages, show error
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            }
        } else {
            // User has permission, allow access
            console.log('✅ Allowing initial access to /dashboard/deposit-form');
            currentRoute.value = initialRoute;
        }
    } else if (initialRoute === '/dashboard/redeem-form') {
        console.log('Initial route is /dashboard/redeem-form, canViewRedeemForms:', canViewRedeemForms.value, 'permissions:', userPermissions.value);
        if (!canViewRedeemForms.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            }
        } else {
            // User has permission, allow access
            console.log('Allowing initial access to /dashboard/redeem-form');
            currentRoute.value = initialRoute;
        }
    } else if (initialRoute === '/dashboard/redeem-process') {
        console.log('Initial route is /dashboard/redeem-process, canViewRedeemProcess:', canViewRedeemProcess.value, 'permissions:', userPermissions.value);
        if (!canViewRedeemProcess.value) {
            if (window.toast) {
                window.toast.error('You do not have permission to access this page.');
            }
            // Redirect to first available page
            if (canAccessDashboard.value) {
                currentRoute.value = '/dashboard';
                window.history.pushState({}, '', '/dashboard');
            } else if (canViewUsers.value) {
                currentRoute.value = '/dashboard/users';
                window.history.pushState({}, '', '/dashboard/users');
            } else if (canViewRoles.value) {
                currentRoute.value = '/dashboard/roles';
                window.history.pushState({}, '', '/dashboard/roles');
            } else if (canViewPages.value) {
                currentRoute.value = '/dashboard/pages';
                window.history.pushState({}, '', '/dashboard/pages');
            } else if (canViewDepositForms.value) {
                currentRoute.value = '/dashboard/deposit-form';
                window.history.pushState({}, '', '/dashboard/deposit-form');
            } else if (canViewRedeemForms.value) {
                currentRoute.value = '/dashboard/redeem-form';
                window.history.pushState({}, '', '/dashboard/redeem-form');
            }
        } else {
            // User has permission, allow access
            console.log('Allowing initial access to /dashboard/redeem-process');
            currentRoute.value = initialRoute;
        }
    } else {
        // Dashboard route with permission, or other routes
        console.log('Setting initial route to:', initialRoute);
        currentRoute.value = initialRoute;
    }
    
    window.addEventListener('popstate', handleRouteChange);
    
    // Watch for currentRoute changes to debug
    watch(currentRoute, (newRoute, oldRoute) => {
        console.log('🔍 currentRoute changed from', oldRoute, 'to', newRoute);
        if (newRoute === '/dashboard' && oldRoute === '/dashboard/users') {
            console.error('❌ Route was reset from /dashboard/users to /dashboard!');
            console.trace('Stack trace for route reset');
        }
    }, { immediate: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', checkScreenSize);
    window.removeEventListener('popstate', handleRouteChange);
});
</script>

