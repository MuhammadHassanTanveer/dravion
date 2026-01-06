<template>
    <li class="inline-flex items-center">
        <slot />
        <span v-if="!isLast" class="mx-2 text-gray-400">/</span>
    </li>
</template>

<script setup>
defineProps({
    isLast: {
        type: Boolean,
        default: false,
    },
});
</script>
