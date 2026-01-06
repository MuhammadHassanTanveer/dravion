<template>
    <Teleport to="body">
        <Transition name="dialog">
            <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="handleCancel">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="handleCancel"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-bold text-gray-900 text-center mb-2">{{ title }}</h3>
                    <p class="text-xs text-gray-600 text-center mb-6">{{ message }}</p>
                    
                    <div class="flex space-x-3">
                        <button
                            @click="handleCancel"
                            class="flex-1 px-4 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]"
                        >
                            No
                        </button>
                        <button
                            @click="handleConfirm"
                            class="flex-1 px-4 py-2.5 rounded-lg bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold hover:from-red-700 hover:to-red-800 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg"
                        >
                            Yes
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm Action',
    },
    message: {
        type: String,
        default: 'Are you sure you want to perform this action?',
    },
});

const emit = defineEmits(['confirm', 'cancel', 'update:isOpen']);

const handleConfirm = () => {
    emit('confirm');
    emit('update:isOpen', false);
};

const handleCancel = () => {
    emit('cancel');
    emit('update:isOpen', false);
};

// Close on Escape key
watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                handleCancel();
            }
        };
        document.addEventListener('keydown', handleEscape);
        return () => {
            document.removeEventListener('keydown', handleEscape);
        };
    }
});
</script>

<style scoped>
.dialog-enter-active,
.dialog-leave-active {
    transition: opacity 0.3s ease;
}

.dialog-enter-active .bg-white,
.dialog-leave-active .bg-white {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.dialog-enter-from,
.dialog-leave-to {
    opacity: 0;
}

.dialog-enter-from .bg-white,
.dialog-leave-to .bg-white {
    transform: scale(0.9) translateY(-20px);
    opacity: 0;
}
</style>

