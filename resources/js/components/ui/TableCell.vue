<template>
    <td :class="cn('p-3 text-xs align-middle [&:has([role=checkbox])]:pr-0', $attrs.class)">
        <slot />
    </td>
</template>

<script setup>
import { cn } from '@/utils/cn.js';
</script>

