<template>
    <a
        v-if="href"
        :href="href"
        @click="handleClick"
        :class="cn(
            'flex items-center space-x-3 px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer transition-colors',
            $attrs.class
        )"
    >
        <slot />
    </a>
    <button
        v-else
        @click="handleClick"
        :class="cn(
            'w-full flex items-center space-x-3 px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer transition-colors text-left',
            $attrs.class
        )"
    >
        <slot />
    </button>
</template>

<script setup>
import { cn } from '@/utils/cn.js';

const props = defineProps({
    href: String,
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
    emit('click', event);
};
</script>

