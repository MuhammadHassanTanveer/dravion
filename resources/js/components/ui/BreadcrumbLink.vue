<template>
    <a 
        v-if="href" 
        :href="href" 
        class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
        @click.prevent="handleClick"
    >
        <slot />
    </a>
    <span v-else class="text-sm font-medium text-gray-900 font-semibold">
        <slot />
    </span>
</template>

<script setup>
const props = defineProps({
    href: String,
    isActive: Boolean,
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
    if (props.href) {
        emit('click', event);
        if (!event.defaultPrevented) {
            window.location.href = props.href;
        }
    }
};
</script>
