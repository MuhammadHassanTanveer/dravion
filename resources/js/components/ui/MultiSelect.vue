<template>
    <div class="relative" ref="dropdownRef">
        <div class="relative">
            <div
                @click="toggleDropdown"
                class="w-full h-10 px-3 py-2 rounded-md border border-gray-300 bg-white cursor-pointer flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="isOpen ? 'ring-2 ring-blue-500 border-blue-500' : ''"
            >
                <div class="flex-1 flex items-center flex-wrap gap-1 min-h-[24px]">
                    <input
                        v-model="searchQuery"
                        @click.stop
                        @focus="isOpen = true"
                        type="text"
                        :placeholder="selectedItems.length > 0 ? '' : placeholder"
                        class="flex-1 min-w-[120px] outline-none text-xs"
                    />
                </div>
                <div class="flex items-center space-x-1">
                    <svg
                        v-if="searchQuery"
                        @click.stop="clearSearch"
                        class="w-4 h-4 text-gray-400 hover:text-gray-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <svg
                        class="w-4 h-4 text-gray-400 transition-transform"
                        :class="{ 'rotate-180': isOpen }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>
        
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 max-h-60 overflow-auto"
            >
                <div v-if="showSelectAll && filteredItems.length > 0" class="sticky top-0 bg-white border-b border-gray-200 px-3 py-2 z-10">
                    <button
                        @click.stop="selectAll"
                        type="button"
                        class="w-full text-left text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-2 py-1 rounded"
                    >
                        Select All ({{ filteredItems.length }})
                    </button>
                </div>
                <div v-if="filteredItems.length === 0" class="px-3 py-2 text-xs text-gray-500">
                    No options found
                </div>
                <div v-else>
                    <div
                        v-for="item in filteredItems"
                        :key="item.id"
                        @click="toggleItem(item)"
                        class="px-3 py-2 text-xs cursor-pointer hover:bg-gray-100 flex items-center space-x-2"
                        :class="{ 'bg-blue-50': isSelected(item) }"
                    >
                        <input
                            type="checkbox"
                            :checked="isSelected(item)"
                            @click.stop
                            class="w-3 h-3 text-blue-600 rounded focus:ring-blue-500"
                            readonly
                        />
                        <span class="flex-1">{{ item[displayKey] }}</span>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    items: {
        type: Array,
        required: true,
    },
    displayKey: {
        type: String,
        default: 'name',
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    placeholder: {
        type: String,
        default: 'Search and select...',
    },
    autoCloseOnSelect: {
        type: Boolean,
        default: false,
    },
    showSelectAll: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);

const selectedItems = computed(() => props.modelValue || []);

const filteredItems = computed(() => {
    if (!searchQuery.value) {
        return props.items;
    }
    const query = searchQuery.value.toLowerCase();
    return props.items.filter(item => {
        const displayValue = item[props.displayKey];
        return displayValue && String(displayValue).toLowerCase().includes(query);
    });
});

const isSelected = (item) => {
    return selectedItems.value.some(selected => selected[props.valueKey] === item[props.valueKey]);
};

const toggleItem = (item) => {
    const currentValue = [...selectedItems.value];
    const index = currentValue.findIndex(selected => selected[props.valueKey] === item[props.valueKey]);
    
    if (index > -1) {
        currentValue.splice(index, 1);
    } else {
        currentValue.push(item);
    }
    
    emit('update:modelValue', currentValue);
    
    // Auto-close dropdown if enabled
    if (props.autoCloseOnSelect) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

const selectAll = () => {
    const allItems = [...filteredItems.value];
    const currentValue = [...selectedItems.value];
    
    // Add all filtered items that aren't already selected
    allItems.forEach(item => {
        const exists = currentValue.some(selected => selected[props.valueKey] === item[props.valueKey]);
        if (!exists) {
            currentValue.push(item);
        }
    });
    
    emit('update:modelValue', currentValue);
    
    // Auto-close dropdown if enabled
    if (props.autoCloseOnSelect) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
    }
};

const clearSearch = () => {
    searchQuery.value = '';
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

