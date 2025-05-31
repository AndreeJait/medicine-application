<!-- @/components/base/Modal.vue -->
<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 transition-opacity"
        @click.self="close"
    >
        <div
            class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative animate-fadeIn"
        >
            <button
                class="absolute top-3 right-3 text-gray-400 hover:text-red-500"
                @click="close"
            >
                <font-awesome-icon icon="times" />
            </button>
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
const emit = defineEmits(["close"]);

const close = () => {
    emit("close");
};
</script>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}
</style>
