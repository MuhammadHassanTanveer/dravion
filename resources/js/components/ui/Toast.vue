<template>
    <Teleport to="body">
        <TransitionGroup
            name="toast"
            tag="div"
            class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto min-w-[400px] max-w-lg w-full bg-white shadow-lg rounded-lg ring-1 ring-black ring-opacity-5 overflow-hidden"
                :class="{
                    'bg-green-50 border-l-4 border-green-400': toast.type === 'success',
                    'bg-red-50 border-l-4 border-red-400': toast.type === 'error',
                    'bg-yellow-50 border-l-4 border-yellow-400': toast.type === 'warning',
                    'bg-blue-50 border-l-4 border-blue-400': toast.type === 'info',
                }"
            >
                <div class="p-5">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg
                                v-if="toast.type === 'success'"
                                class="h-6 w-6 text-green-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg
                                v-else-if="toast.type === 'error'"
                                class="h-6 w-6 text-red-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg
                                v-else-if="toast.type === 'warning'"
                                class="h-6 w-6 text-yellow-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <svg
                                v-else
                                class="h-6 w-6 text-blue-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1 pt-0.5 min-w-0">
                            <p
                                class="text-xs font-medium break-words"
                                :class="{
                                    'text-green-800': toast.type === 'success',
                                    'text-red-800': toast.type === 'error',
                                    'text-yellow-800': toast.type === 'warning',
                                    'text-blue-800': toast.type === 'info',
                                }"
                            >
                                {{ toast.message }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button
                                @click="removeToast(toast.id)"
                                class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="{
                                    'text-green-400 hover:text-green-500 focus:ring-green-500': toast.type === 'success',
                                    'text-red-400 hover:text-red-500 focus:ring-red-500': toast.type === 'error',
                                    'text-yellow-400 hover:text-yellow-500 focus:ring-yellow-500': toast.type === 'warning',
                                    'text-blue-400 hover:text-blue-500 focus:ring-blue-500': toast.type === 'info',
                                }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </TransitionGroup>
    </Teleport>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const toasts = ref([]);
let toastIdCounter = 0;

const addToast = (message, type = 'info', duration = 5000) => {
    const id = ++toastIdCounter;
    const toast = { id, message, type };
    toasts.value.push(toast);

    if (duration > 0) {
        setTimeout(() => {
            removeToast(id);
        }, duration);
    }

    return id;
};

const removeToast = (id) => {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

// Expose methods globally
window.toast = {
    success: (message, duration) => addToast(message, 'success', duration),
    error: (message, duration) => addToast(message, 'error', duration),
    warning: (message, duration) => addToast(message, 'warning', duration),
    info: (message, duration) => addToast(message, 'info', duration),
};

onMounted(() => {
    // Toast is ready
});

onBeforeUnmount(() => {
    delete window.toast;
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>

