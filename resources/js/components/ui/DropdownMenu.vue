<template>
    <div class="relative" ref="dropdownRef">
        <div @click="toggle">
            <slot name="trigger" />
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
                class="absolute w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 overflow-hidden"
                :class="[
                    positionClass,
                    position === 'top-right' || position === 'top-left' ? 'bottom-full mb-2' : 'top-full mt-2',
                    position === 'bottom-right' || position === 'top-right' ? 'right-0' : 'left-0'
                ]"
            >
                <slot name="header" />
                <div class="py-1">
                    <slot />
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    position: {
        type: String,
        default: 'bottom-right',
        validator: (value) => ['bottom-right', 'bottom-left', 'top-right', 'top-left'].includes(value),
    },
});

const isOpen = ref(false);
const dropdownRef = ref(null);

const positionClass = {
    'bottom-right': 'origin-top-right',
    'bottom-left': 'origin-top-left',
    'top-right': 'origin-bottom-right',
    'top-left': 'origin-bottom-left',
}[props.position];

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const close = () => {
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

defineExpose({ close, toggle });
</script>

