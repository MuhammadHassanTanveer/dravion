<template>
    <div class="w-full">
        <div class="mb-4 sm:mb-6">
            <div class="flex items-center space-x-2 text-xs sm:text-sm mb-2">
                <span class="text-gray-900 font-semibold">Dashboard</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2 sm:mt-4">Dashboard</h1>
        </div>

        <!-- Super Admin Dashboard -->
        <div v-if="isSuperAdmin">
            <!-- Statistics Cards: Total Users, Total Roles, Active Sessions, Permissions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total Users</h3>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ stats.totalUsers }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total Roles</h3>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ stats.totalRoles }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Active Sessions</h3>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ stats.activeSessions }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Permissions</h3>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-orange-100 flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ stats.totalPermissions }}</p>
                </div>
            </div>

            <!-- Deposit/Redeem/Profit Section with Date Filter -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 border border-gray-100">
                <!-- Date Range Filter -->
                <div class="mb-4 sm:mb-6">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <div class="max-w-xs">
                        <DateRangePicker
                            v-model="filterDateRange"
                            placeholder="Select date range"
                            @update:modelValue="handleDateRangeChange"
                        />
                    </div>
                </div>

                <!-- Total Deposit, Total Redeem, and Profit/Loss Blocks (Same Row) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                <!-- Total Deposit Block -->
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-cyan-100 flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total Deposit</h3>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ formatCurrency(stats.totalDepositSum) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-3 mt-3 max-h-48 overflow-y-auto">
                        <div 
                            v-for="company in stats.depositsByCompany" 
                            :key="'deposit-' + company.company_id"
                            class="flex items-center justify-between py-1.5 text-xs"
                        >
                            <span class="text-gray-600 font-medium">{{ company.company_name }}</span>
                            <span class="text-gray-900 font-semibold">{{ formatCurrency(company.total_deposit) }}</span>
                        </div>
                        <div v-if="stats.depositsByCompany.length === 0" class="text-center py-2 text-gray-500 text-xs">
                            No companies available
                        </div>
                    </div>
                </div>

                <!-- Total Redeem Block -->
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total Redeem (Paid)</h3>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ formatCurrency(stats.totalRedeemSum) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-3 mt-3 max-h-48 overflow-y-auto">
                        <div 
                            v-for="company in stats.redeemsByCompany" 
                            :key="'redeem-' + company.company_id"
                            class="flex items-center justify-between py-1.5 text-xs"
                        >
                            <span class="text-gray-600 font-medium">{{ company.company_name }}</span>
                            <span class="text-gray-900 font-semibold">{{ formatCurrency(company.total_redeem) }}</span>
                        </div>
                        <div v-if="stats.redeemsByCompany.length === 0" class="text-center py-2 text-gray-500 text-xs">
                            No companies available
                        </div>
                    </div>
                </div>

                <!-- Profit and Loss Block -->
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Profit and Loss</h3>
                                <p 
                                    class="text-xl sm:text-2xl font-bold"
                                    :class="stats.totalProfitLoss >= 0 ? 'text-green-600' : 'text-red-600'"
                                >
                                    {{ formatCurrency(stats.totalProfitLoss) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-3 mt-3 max-h-48 overflow-y-auto">
                        <div 
                            v-for="company in stats.profitLossByCompany" 
                            :key="'profit-' + company.company_id"
                            class="flex items-center justify-between py-1.5 text-xs"
                        >
                            <span class="text-gray-600 font-medium">{{ company.company_name }}</span>
                            <span 
                                class="font-semibold"
                                :class="company.profit_loss >= 0 ? 'text-green-600' : 'text-red-600'"
                            >
                                {{ formatCurrency(company.profit_loss) }}
                            </span>
                        </div>
                        <div v-if="stats.profitLossByCompany.length === 0" class="text-center py-2 text-gray-500 text-xs">
                            No companies available
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Page-wise Statistics Section with Filters -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 border border-gray-100">
                <!-- Title -->
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    Page-wise Statistics
                    <span v-if="selectedPageName" class="text-base sm:text-lg font-semibold text-gray-600">: {{ selectedPageName }}</span>
                </h2>
                
                <!-- Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                    <!-- Date Range Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                        <DateRangePicker
                            v-model="pageFilterDateRange"
                            placeholder="Select date range"
                            @update:modelValue="handlePageFilterDateRangeChange"
                        />
                    </div>

                    <!-- Company Filter (Super Admin Only) -->
                    <div v-if="isSuperAdmin">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Company</label>
                        <div class="relative" ref="filterCompanyDropdownRef">
                            <Input 
                                :value="filterCompanyName || filterCompanySearchQuery"
                                @focus="showFilterCompanyDropdown = true"
                                @input="handleCompanySearchInput"
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
                                        v-if="filterCompanyName"
                                        @click="clearFilterCompany"
                                        class="px-3 py-2 text-xs text-gray-500 hover:bg-gray-100 cursor-pointer border-t border-gray-200"
                                    >
                                        Clear Selection
                                    </li>
                                </ul>
                            </div>
                            <div v-if="showFilterCompanyDropdown && filteredFilterCompanies.length === 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="px-3 py-2 text-xs text-gray-500 text-center">
                                    No companies available
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Page Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Page</label>
                        <div class="relative" ref="filterPageDropdownRef">
                            <Input 
                                :value="filterPageName || filterPageSearchQuery"
                                @focus="handlePageInputFocus"
                                @input="handlePageSearchInput"
                                :placeholder="isSuperAdmin && !filterCompanyId ? 'Please select company first' : 'Search and select page...'"
                                :disabled="isSuperAdmin && !filterCompanyId"
                                class="text-xs"
                                :class="isSuperAdmin && !filterCompanyId ? 'bg-gray-100 cursor-not-allowed' : ''"
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
                                        v-if="filterPageName"
                                        @click="clearFilterPage"
                                        class="px-3 py-2 text-xs text-gray-500 hover:bg-gray-100 cursor-pointer border-t border-gray-200"
                                    >
                                        Clear Selection
                                    </li>
                                </ul>
                            </div>
                            <div v-if="showFilterPageDropdown && filteredFilterPages.length === 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="px-3 py-2 text-xs text-gray-500 text-center">
                                    <span v-if="isSuperAdmin && !filterCompanyId">
                                        Please select a company first
                                    </span>
                                    <span v-else>
                                        No pages available
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Statistics Cards (When Page is Selected) -->
                <div v-if="selectedPageId" class="mt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                        <!-- Page Total Deposit -->
                        <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-cyan-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Page Deposit</h3>
                                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ formatCurrency(pageStats.totalDepositSum) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Page Total Redeem -->
                        <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Page Redeem (Paid)</h3>
                                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ formatCurrency(pageStats.totalRedeemSum) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Page Profit and Loss -->
                        <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Page Profit/Loss</h3>
                                        <p 
                                            class="text-xl sm:text-2xl font-bold"
                                            :class="pageStats.profitLoss >= 0 ? 'text-green-600' : 'text-red-600'"
                                        >
                                            {{ formatCurrency(pageStats.profitLoss) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Non-Super Admin Dashboard (Company Users) -->
        <div v-else>
            <!-- Deposit/Redeem/Profit Section with Date Filter -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 border border-gray-100">
                <!-- Date Range Filter -->
                <div class="mb-4 sm:mb-6">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <div class="max-w-xs">
                        <DateRangePicker
                            v-model="filterDateRange"
                            placeholder="Select date range"
                            @update:modelValue="handleDateRangeChange"
                        />
                    </div>
                </div>

                <!-- Total Deposit, Total Redeem, and Profit/Loss Blocks (Same Row) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                <!-- Total Deposit Block -->
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-cyan-100 flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total Deposit</h3>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ formatCurrency(stats.totalDepositSum) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Redeem Block -->
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total Redeem (Paid)</h3>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ formatCurrency(stats.totalRedeemSum) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profit and Loss Block -->
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Profit and Loss</h3>
                                <p 
                                    class="text-xl sm:text-2xl font-bold"
                                    :class="stats.profitLoss >= 0 ? 'text-green-600' : 'text-red-600'"
                                >
                                    {{ formatCurrency(stats.profitLoss) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Page-wise Statistics Section with Filters -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 border border-gray-100">
                <!-- Title -->
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    Page-wise Statistics
                    <span v-if="selectedPageName" class="text-base sm:text-lg font-semibold text-gray-600">: {{ selectedPageName }}</span>
                </h2>
                
                <!-- Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                    <!-- Date Range Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                        <DateRangePicker
                            v-model="pageFilterDateRange"
                            placeholder="Select date range"
                            @update:modelValue="handlePageFilterDateRangeChange"
                        />
                    </div>

                    <!-- Page Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Page</label>
                        <div class="relative" ref="filterPageDropdownRef">
                            <Input 
                                :value="filterPageName || filterPageSearchQuery"
                                @focus="handlePageInputFocus"
                                @input="handlePageSearchInput"
                                :placeholder="isSuperAdmin && !filterCompanyId ? 'Please select company first' : 'Search and select page...'"
                                :disabled="isSuperAdmin && !filterCompanyId"
                                class="text-xs"
                                :class="isSuperAdmin && !filterCompanyId ? 'bg-gray-100 cursor-not-allowed' : ''"
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
                                        v-if="filterPageName"
                                        @click="clearFilterPage"
                                        class="px-3 py-2 text-xs text-gray-500 hover:bg-gray-100 cursor-pointer border-t border-gray-200"
                                    >
                                        Clear Selection
                                    </li>
                                </ul>
                            </div>
                            <div v-if="showFilterPageDropdown && filteredFilterPages.length === 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="px-3 py-2 text-xs text-gray-500 text-center">
                                    <span v-if="isSuperAdmin && !filterCompanyId">
                                        Please select a company first
                                    </span>
                                    <span v-else>
                                        No pages available
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Statistics Cards (When Page is Selected) -->
                <div v-if="selectedPageId" class="mt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                        <!-- Page Total Deposit -->
                        <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-cyan-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Page Deposit</h3>
                                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ formatCurrency(pageStats.totalDepositSum) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Page Total Redeem -->
                        <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Page Redeem (Paid)</h3>
                                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ formatCurrency(pageStats.totalRedeemSum) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Page Profit and Loss -->
                        <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-medium text-gray-500">Page Profit/Loss</h3>
                                        <p 
                                            class="text-xl sm:text-2xl font-bold"
                                            :class="pageStats.profitLoss >= 0 ? 'text-green-600' : 'text-red-600'"
                                        >
                                            {{ formatCurrency(pageStats.profitLoss) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';
import DateRangePicker from '../ui/DateRangePicker.vue';
import Input from '../ui/Input.vue';

const stats = ref({
    totalUsers: 0,
    totalRoles: 0,
    activeSessions: 0,
    totalPermissions: 0,
    totalDepositSum: 0,
    depositsByCompany: [],
    totalRedeemSum: 0,
    redeemsByCompany: [],
    totalProfitLoss: 0,
    profitLossByCompany: [],
    profitLoss: 0,
});

const pageStats = ref({
    totalDepositSum: 0,
    totalRedeemSum: 0,
    profitLoss: 0,
});

const userPermissions = ref([]);
const currentUser = ref({
    role: '',
    company_id: null,
});

// Filters for Deposit/Redeem/Profit cards
const filterDateRange = ref({ startDate: null, endDate: null });

// Filters for Page-wise statistics
const pageFilterDateRange = ref({ startDate: null, endDate: null });
const filterCompanyId = ref(null);
const filterCompanyName = ref('');
const filterCompanySearchQuery = ref('');
const showFilterCompanyDropdown = ref(false);
const filterCompanyDropdownRef = ref(null);
const availableCompanies = ref([]);

const selectedPageId = ref(null);
const selectedPageName = ref('');
const filterPageId = ref(null);
const filterPageName = ref('');
const filterPageSearchQuery = ref('');
const showFilterPageDropdown = ref(false);
const filterPageDropdownRef = ref(null);
const availablePages = ref([]);

const isSuperAdmin = computed(() => {
    return currentUser.value.role === 'super admin';
});

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

const formatCurrency = (amount) => {
    if (!amount && amount !== 0) return '0.00';
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

// Calculate percentages for bar graph
const maxValue = computed(() => {
    return Math.max(stats.value.totalDepositSum, stats.value.totalRedeemSum, 1);
});

const depositPercentage = computed(() => {
    if (maxValue.value === 0) return 0;
    return (stats.value.totalDepositSum / maxValue.value) * 100;
});

const redeemPercentage = computed(() => {
    if (maxValue.value === 0) return 0;
    return (stats.value.totalRedeemSum / maxValue.value) * 100;
});

// Filter companies for dropdown
const filteredFilterCompanies = computed(() => {
    if (!filterCompanySearchQuery.value) {
        return availableCompanies.value;
    }
    const query = filterCompanySearchQuery.value.toLowerCase();
    return availableCompanies.value.filter(company =>
        company.company_name.toLowerCase().includes(query)
    );
});

// Filter pages for dropdown
const filteredFilterPages = computed(() => {
    // For super admin, require company selection first
    if (isSuperAdmin.value && !filterCompanyId.value) {
        return [];
    }
    
    let pages = availablePages.value;
    
    // Filter by company if super admin has selected a company
    if (isSuperAdmin.value && filterCompanyId.value) {
        pages = pages.filter(page => page.company_id === filterCompanyId.value);
    } else if (!isSuperAdmin.value && currentUser.value.company_id) {
        pages = pages.filter(page => page.company_id === currentUser.value.company_id);
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

const loadUserPermissions = async () => {
    try {
        const response = await axios.get('/user/permissions');
        userPermissions.value = response.data.permissions || [];
        
        // Update current user info
        if (response.data.user) {
            currentUser.value = {
                role: response.data.user.role || '',
                company_id: response.data.user.company_id || null,
            };
        }
    } catch (error) {
        console.error('Error loading permissions:', error);
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

const loadPages = async (companyId = null) => {
    try {
        // For super admin, only load pages if company is selected
        if (isSuperAdmin.value && !companyId) {
            availablePages.value = [];
            return;
        }
        
        let url = '/pages';
        if (isSuperAdmin.value && companyId) {
            url += `?company_id=${companyId}`;
        } else if (!isSuperAdmin.value && currentUser.value.company_id) {
            url += `?company_id=${currentUser.value.company_id}`;
        }
        const response = await axios.get(url);
        availablePages.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Error loading pages:', error);
    }
};

const selectFilterCompany = async (company) => {
    filterCompanyId.value = company.id;
    filterCompanyName.value = company.company_name;
    filterCompanySearchQuery.value = company.company_name;
    showFilterCompanyDropdown.value = false;
    
    // Reload pages filtered by selected company
    await loadPages(company.id);
    
    // Clear page filter when company changes
    filterPageId.value = null;
    filterPageName.value = '';
    filterPageSearchQuery.value = '';
    selectedPageId.value = null;
    selectedPageName.value = '';
    
    // Clear page stats, but don't reload main stats (they should remain independent)
    pageStats.value = {
        totalDepositSum: 0,
        totalRedeemSum: 0,
        profitLoss: 0,
    };
};

const clearFilterCompany = async () => {
    filterCompanyId.value = null;
    filterCompanyName.value = '';
    filterCompanySearchQuery.value = '';
    showFilterCompanyDropdown.value = false;
    
    // For super admin, clear pages when company is cleared
    if (isSuperAdmin.value) {
        availablePages.value = [];
    } else {
        // Reload all pages for non-super admin
        await loadPages();
    }
    
    // Clear page filter
    filterPageId.value = null;
    filterPageName.value = '';
    filterPageSearchQuery.value = '';
    selectedPageId.value = null;
    selectedPageName.value = '';
    
    // Clear page stats, but don't reload main stats (they should remain independent)
    pageStats.value = {
        totalDepositSum: 0,
        totalRedeemSum: 0,
        profitLoss: 0,
    };
};

const selectFilterPage = async (page) => {
    filterPageId.value = page.id;
    filterPageName.value = page.page_name;
    filterPageSearchQuery.value = page.page_name;
    selectedPageId.value = page.id;
    selectedPageName.value = page.page_name;
    showFilterPageDropdown.value = false;
    
    // Only reload page stats, NOT main stats (main stats should remain independent)
    await loadPageStats();
};

const clearFilterPage = async () => {
    filterPageId.value = null;
    filterPageName.value = '';
    filterPageSearchQuery.value = '';
    selectedPageId.value = null;
    selectedPageName.value = '';
    showFilterPageDropdown.value = false;
    
    // Clear page stats, but don't reload main stats (they should remain independent)
    pageStats.value = {
        totalDepositSum: 0,
        totalRedeemSum: 0,
        profitLoss: 0,
    };
};

const handleDateRangeChange = async () => {
    await loadStats();
};

const handlePageFilterDateRangeChange = async () => {
    if (selectedPageId.value) {
        await loadPageStats();
    }
};


const handleCompanySearchInput = (event) => {
    filterCompanySearchQuery.value = event.target.value;
    if (!filterCompanyName.value) {
        showFilterCompanyDropdown.value = true;
    } else {
        // If a company is selected, clear it when user starts typing
        filterCompanyName.value = '';
        filterCompanyId.value = null;
        showFilterCompanyDropdown.value = true;
    }
};

const handlePageInputFocus = () => {
    // For super admin, require company selection first
    if (isSuperAdmin.value && !filterCompanyId.value) {
        showFilterPageDropdown.value = false;
        return;
    }
    
    // Clear search query when focusing to show all pages
    if (filterPageName.value) {
        filterPageSearchQuery.value = '';
    }
    showFilterPageDropdown.value = true;
};

const handlePageSearchInput = (event) => {
    filterPageSearchQuery.value = event.target.value;
    if (!filterPageName.value) {
        showFilterPageDropdown.value = true;
    } else {
        // If a page is selected, clear it when user starts typing
        filterPageName.value = '';
        selectedPageId.value = null;
        selectedPageName.value = '';
        showFilterPageDropdown.value = true;
    }
};

const formatDateForAPI = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const loadSuperAdminStats = async () => {
    try {
        const params = {};
        // Only use date filter from the main section, not page or company filters
        if (filterDateRange.value.startDate && filterDateRange.value.endDate) {
            params.start_date = formatDateForAPI(filterDateRange.value.startDate);
            params.end_date = formatDateForAPI(filterDateRange.value.endDate);
        }
        // Do NOT include filterCompanyId or filterPageId here - they are only for page-wise stats
        
        const response = await axios.get('/dashboard/stats', { params });
        stats.value = {
            totalUsers: response.data.totalUsers || 0,
            totalRoles: response.data.totalRoles || 0,
            activeSessions: response.data.activeSessions || 0,
            totalPermissions: response.data.totalPermissions || 0,
            totalDepositSum: response.data.totalDepositSum || 0,
            depositsByCompany: response.data.depositsByCompany || [],
            totalRedeemSum: response.data.totalRedeemSum || 0,
            redeemsByCompany: response.data.redeemsByCompany || [],
            totalProfitLoss: response.data.totalProfitLoss || 0,
            profitLossByCompany: response.data.profitLossByCompany || [],
        };
    } catch (error) {
        console.error('Error loading super admin stats:', error);
        if (error.response?.status === 403) {
            await loadRegularStats();
        }
    }
};

const loadRegularStats = async () => {
    try {
        const params = {};
        // Only use date filter from the main section, not page filter
        if (filterDateRange.value.startDate && filterDateRange.value.endDate) {
            params.start_date = formatDateForAPI(filterDateRange.value.startDate);
            params.end_date = formatDateForAPI(filterDateRange.value.endDate);
        }
        // Do NOT include filterPageId here - it is only for page-wise stats
        
        const response = await axios.get('/dashboard/company-stats', { params });
        stats.value.totalDepositSum = response.data.totalDepositSum || 0;
        stats.value.totalRedeemSum = response.data.totalRedeemSum || 0;
        stats.value.profitLoss = response.data.profitLoss || 0;
    } catch (error) {
        console.error('Error loading company stats:', error);
    }
};

const loadPageStats = async () => {
    if (!selectedPageId.value) {
        pageStats.value = {
            totalDepositSum: 0,
            totalRedeemSum: 0,
            profitLoss: 0,
        };
        return;
    }
    
    try {
        const params = { page_id: selectedPageId.value };
        // Use pageFilterDateRange for page-wise statistics
        if (pageFilterDateRange.value.startDate && pageFilterDateRange.value.endDate) {
            params.start_date = formatDateForAPI(pageFilterDateRange.value.startDate);
            params.end_date = formatDateForAPI(pageFilterDateRange.value.endDate);
        }
        if (isSuperAdmin.value && filterCompanyId.value) {
            params.company_id = filterCompanyId.value;
        }
        
        if (isSuperAdmin.value) {
            const response = await axios.get('/dashboard/stats', { params });
            pageStats.value = {
                totalDepositSum: response.data.totalDepositSum || 0,
                totalRedeemSum: response.data.totalRedeemSum || 0,
                profitLoss: (response.data.totalDepositSum || 0) - (response.data.totalRedeemSum || 0),
            };
        } else {
            const response = await axios.get('/dashboard/company-stats', { params });
            pageStats.value = {
                totalDepositSum: response.data.totalDepositSum || 0,
                totalRedeemSum: response.data.totalRedeemSum || 0,
                profitLoss: response.data.profitLoss || 0,
            };
        }
    } catch (error) {
        console.error('Error loading page stats:', error);
    }
};

const loadStats = async () => {
    if (isSuperAdmin.value) {
        await loadSuperAdminStats();
    } else {
        await loadRegularStats();
    }
};

// Handle click outside for dropdowns
const handleClickOutside = (event) => {
    if (filterCompanyDropdownRef.value && !filterCompanyDropdownRef.value.contains(event.target)) {
        showFilterCompanyDropdown.value = false;
    }
    if (filterPageDropdownRef.value && !filterPageDropdownRef.value.contains(event.target)) {
        showFilterPageDropdown.value = false;
    }
};

onMounted(async () => {
    await loadUserPermissions();
    
    if (isSuperAdmin.value) {
        await loadCompanies();
        // Don't load pages for super admin until company is selected
        availablePages.value = [];
    } else {
        await loadPages();
    }
    
    await loadStats();
    
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Watch for page filter changes
watch(filterPageId, async (newPageId) => {
    if (newPageId) {
        await loadPageStats();
    } else {
        pageStats.value = {
            totalDepositSum: 0,
            totalRedeemSum: 0,
            profitLoss: 0,
        };
    }
});

</script>
