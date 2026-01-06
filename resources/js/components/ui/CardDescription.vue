<template>
    <p :class="cn('text-xs text-gray-500', $attrs.class)">
        <slot />
    </p>
</template>

<script setup>
import { cn } from '@/utils/cn';
</script>

