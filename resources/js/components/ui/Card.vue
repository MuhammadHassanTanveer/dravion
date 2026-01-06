<template>
    <div :class="cn('rounded-lg border border-gray-200 bg-white text-gray-900 shadow-sm', $attrs.class)">
        <slot />
    </div>
</template>

<script setup>
import { cn } from '@/utils/cn';
</script>

