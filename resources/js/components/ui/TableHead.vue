<template>
    <th :class="cn('h-10 px-4 text-left align-middle font-medium text-xs text-muted-foreground [&:has([role=checkbox])]:pr-0', $attrs.class)">
        <slot />
    </th>
</template>

<script setup>
import { cn } from '@/utils/cn.js';
</script>

