<template>
    <div class="relative" ref="datePickerRef">
        <!-- Input Field -->
        <div class="relative">
            <Input
                :value="displayValue"
                type="text"
                :placeholder="placeholder"
                readonly
                class="cursor-pointer pr-8"
                @click="toggleCalendar"
            />
            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-1">
                <button
                    v-if="startDate && endDate"
                    @click.stop="clearSelection"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                    type="button"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Calendar Overlay -->
        <div
            v-if="isOpen"
            class="fixed bg-white border border-gray-300 rounded-lg shadow-xl z-[9999] p-4"
            :style="calendarStyle"
        >
            <!-- Date Range Display -->
            <div class="mb-4">
                <div class="flex items-center space-x-2">
                    <input
                        v-model="dateRangeText"
                        type="text"
                        placeholder="Search by date"
                        class="flex-1 px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                        @input="parseDateRange"
                    />
                    <button
                        v-if="startDate && endDate"
                        @click="clearSelection"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1"
                        type="button"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Two Calendars Side by Side -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <!-- Left Calendar (Start Date) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <button
                            @click="previousMonth('left')"
                            class="p-1 hover:bg-gray-100 rounded transition-colors"
                            type="button"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <span class="text-xs font-semibold text-gray-700">{{ formatMonthYear(leftCalendarDate) }}</span>
                        <button
                            @click="nextMonth('left')"
                            class="p-1 hover:bg-gray-100 rounded transition-colors"
                            type="button"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-xs">
                        <div class="text-center text-gray-500 font-medium py-1" v-for="day in weekDays" :key="day">{{ day }}</div>
                        <div
                            v-for="day in leftCalendarDays"
                            :key="`left-${day.date}`"
                            @click="selectDate(day.date, 'start')"
                            class="text-center py-2 rounded cursor-pointer transition-colors"
                            :class="getDayClasses(day, 'left')"
                        >
                            {{ day.day }}
                        </div>
                    </div>
                </div>

                <!-- Right Calendar (End Date) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <button
                            @click="previousMonth('right')"
                            class="p-1 hover:bg-gray-100 rounded transition-colors"
                            type="button"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <span class="text-xs font-semibold text-gray-700">{{ formatMonthYear(rightCalendarDate) }}</span>
                        <button
                            @click="nextMonth('right')"
                            class="p-1 hover:bg-gray-100 rounded transition-colors"
                            type="button"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-xs">
                        <div class="text-center text-gray-500 font-medium py-1" v-for="day in weekDays" :key="day">{{ day }}</div>
                        <div
                            v-for="day in rightCalendarDays"
                            :key="`right-${day.date}`"
                            @click="selectDate(day.date, 'end')"
                            class="text-center py-2 rounded cursor-pointer transition-colors"
                            :class="getDayClasses(day, 'right')"
                        >
                            {{ day.day }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- OK and Clear Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-200">
                <button
                    @click="clearSelection"
                    type="button"
                    class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                >
                    Clear
                </button>
                <button
                    @click="applySelection"
                    type="button"
                    class="px-4 py-2 text-xs font-medium text-white bg-blue-600 border border-blue-600 rounded-md hover:bg-blue-700 transition-colors"
                >
                    OK
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import Input from './Input.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ startDate: null, endDate: null }),
    },
    placeholder: {
        type: String,
        default: 'Select date range',
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const datePickerRef = ref(null);
const leftCalendarDate = ref(new Date());
const rightCalendarDate = ref(new Date());
const startDate = ref(props.modelValue?.startDate || null);
const endDate = ref(props.modelValue?.endDate || null);
const dateRangeText = ref('');
const calendarPosition = ref({ top: 0, left: 0 });

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const calendarStyle = computed(() => {
    return {
        width: '600px',
        top: `${calendarPosition.value.top}px`,
        left: `${calendarPosition.value.left}px`,
    };
});

// Initialize right calendar to next month
onMounted(() => {
    const today = new Date();
    leftCalendarDate.value = new Date(today.getFullYear(), today.getMonth(), 1);
    rightCalendarDate.value = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    updateDateRangeText();
});

const formatMonthYear = (date) => {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    return `${date.getFullYear()} ${months[date.getMonth()]}`;
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const displayValue = computed(() => {
    if (startDate.value && endDate.value) {
        return `${formatDate(startDate.value)} To ${formatDate(endDate.value)}`;
    }
    return '';
});

const updateDateRangeText = () => {
    if (startDate.value && endDate.value) {
        dateRangeText.value = `${formatDate(startDate.value)} To ${formatDate(endDate.value)}`;
    } else {
        dateRangeText.value = '';
    }
};

const parseDateRange = () => {
    const parts = dateRangeText.value.split(' To ');
    if (parts.length === 2) {
        const start = new Date(parts[0].trim());
        const end = new Date(parts[1].trim());
        if (!isNaN(start.getTime()) && !isNaN(end.getTime())) {
            startDate.value = start;
            endDate.value = end;
            leftCalendarDate.value = new Date(start.getFullYear(), start.getMonth(), 1);
            rightCalendarDate.value = new Date(end.getFullYear(), end.getMonth(), 1);
            emitValue();
        }
    }
};

const getCalendarDays = (date) => {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    const days = [];
    
    // Previous month days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        days.push({
            day: prevMonthLastDay - i,
            date: new Date(year, month - 1, prevMonthLastDay - i),
            isCurrentMonth: false,
        });
    }
    
    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        days.push({
            day: i,
            date: new Date(year, month, i),
            isCurrentMonth: true,
        });
    }
    
    // Next month days
    const remainingDays = 42 - days.length; // 6 rows * 7 days
    for (let i = 1; i <= remainingDays; i++) {
        days.push({
            day: i,
            date: new Date(year, month + 1, i),
            isCurrentMonth: false,
        });
    }
    
    return days;
};

const leftCalendarDays = computed(() => getCalendarDays(leftCalendarDate.value));
const rightCalendarDays = computed(() => getCalendarDays(rightCalendarDate.value));

const getDayClasses = (day, calendar) => {
    const classes = [];
    const dayDate = formatDate(day.date);
    const startDateStr = startDate.value ? formatDate(startDate.value) : '';
    const endDateStr = endDate.value ? formatDate(endDate.value) : '';
    
    if (!day.isCurrentMonth) {
        classes.push('text-gray-300');
    } else {
        classes.push('text-gray-700 hover:bg-gray-100');
    }
    
    if (dayDate === startDateStr || dayDate === endDateStr) {
        classes.push('bg-blue-600 text-white font-semibold');
    } else if (startDate.value && endDate.value) {
        const dayTime = day.date.getTime();
        const startTime = startDate.value.getTime();
        const endTime = endDate.value.getTime();
        if (dayTime >= startTime && dayTime <= endTime && day.isCurrentMonth) {
            classes.push('bg-blue-100 text-blue-700');
        }
    }
    
    return classes.join(' ');
};

const selectDate = (date, type) => {
    if (!startDate.value || (startDate.value && endDate.value)) {
        // Start new selection
        startDate.value = new Date(date);
        endDate.value = null;
    } else {
        // Complete the range
        if (date < startDate.value) {
            endDate.value = new Date(startDate.value);
            startDate.value = new Date(date);
        } else {
            endDate.value = new Date(date);
        }
        updateDateRangeText();
    }
};

const applySelection = () => {
    if (startDate.value && endDate.value) {
        emitValue();
        isOpen.value = false;
    }
};

const previousMonth = (calendar) => {
    if (calendar === 'left') {
        leftCalendarDate.value = new Date(leftCalendarDate.value.getFullYear(), leftCalendarDate.value.getMonth() - 1, 1);
    } else {
        rightCalendarDate.value = new Date(rightCalendarDate.value.getFullYear(), rightCalendarDate.value.getMonth() - 1, 1);
    }
};

const nextMonth = (calendar) => {
    if (calendar === 'left') {
        leftCalendarDate.value = new Date(leftCalendarDate.value.getFullYear(), leftCalendarDate.value.getMonth() + 1, 1);
    } else {
        rightCalendarDate.value = new Date(rightCalendarDate.value.getFullYear(), rightCalendarDate.value.getMonth() + 1, 1);
    }
};

const updateCalendarPosition = () => {
    if (datePickerRef.value && isOpen.value) {
        const rect = datePickerRef.value.getBoundingClientRect();
        
        // Position below the input field (viewport coordinates for fixed positioning)
        let top = rect.bottom + 8; // 8px margin
        let left = rect.left;
        
        // Check if calendar would go off the right edge
        const calendarWidth = 600;
        if (left + calendarWidth > window.innerWidth) {
            left = window.innerWidth - calendarWidth - 10; // 10px margin from edge
        }
        
        // Check if calendar would go off the bottom edge
        const calendarHeight = 400; // Approximate height
        if (top + calendarHeight > window.innerHeight) {
            // Position above the input field instead
            top = rect.top - calendarHeight - 8;
        }
        
        // Ensure calendar doesn't go off the top edge
        if (top < 10) {
            top = 10;
        }
        
        // Ensure calendar doesn't go off the left edge
        if (left < 10) {
            left = 10;
        }
        
        calendarPosition.value = { top, left };
    }
};

const toggleCalendar = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        updateDateRangeText();
        // Use nextTick to ensure DOM is updated
        setTimeout(() => {
            updateCalendarPosition();
        }, 0);
    }
};

const clearSelection = () => {
    startDate.value = null;
    endDate.value = null;
    dateRangeText.value = '';
    emitValue();
};

const emitValue = () => {
    emit('update:modelValue', {
        startDate: startDate.value,
        endDate: endDate.value,
    });
};

const handleClickOutside = (event) => {
    if (datePickerRef.value && !datePickerRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('scroll', updateCalendarPosition, true);
    window.addEventListener('resize', updateCalendarPosition);
    if (props.modelValue?.startDate) {
        startDate.value = new Date(props.modelValue.startDate);
    }
    if (props.modelValue?.endDate) {
        endDate.value = new Date(props.modelValue.endDate);
    }
    updateDateRangeText();
});

watch(isOpen, (newValue) => {
    if (newValue) {
        nextTick(() => {
            updateCalendarPosition();
        });
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('scroll', updateCalendarPosition, true);
    window.removeEventListener('resize', updateCalendarPosition);
});
</script>

