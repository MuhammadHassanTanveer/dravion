<template>
    <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :class="cn(
            'flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs file:border-0 file:bg-transparent file:text-xs file:font-medium placeholder:text-gray-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
            $attrs.class
        )"
        v-bind="$attrs"
    />
</template>

<script setup>
import { cn } from '@/utils/cn.js';

defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
});

defineEmits(['update:modelValue']);
</script>

