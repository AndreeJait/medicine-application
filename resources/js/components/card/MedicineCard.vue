<template>
    <div
        class="group bg-white rounded-lg shadow hover:shadow-lg transition cursor-pointer overflow-hidden flex flex-col relative"
        @click="$router.push(`/admin/medicine/stock/${medicine.id}`)"
    >
        <!-- Image Carousel or No Image -->
        <div class="relative h-48 bg-gray-100">
            <template v-if="imageUrls.length">
                <img
                    :src="imageUrls[currentImage]"
                    class="w-full h-full object-cover transition-opacity duration-300"
                />
                <span
                    v-if="imageUrls.length > 1"
                    class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-0.5 rounded"
                >
                    {{ currentImage + 1 }} / {{ imageUrls.length }}
                </span>
                <div
                    v-if="imageUrls.length > 1"
                    class="absolute inset-0 flex justify-between items-center px-2"
                >
                    <button @click.stop="prevImage" class="text-white bg-black/40 p-1 rounded-full">‹</button>
                    <button @click.stop="nextImage" class="text-white bg-black/40 p-1 rounded-full">›</button>
                </div>
            </template>
            <template v-else>
                <div class="w-full h-full flex items-center justify-center text-gray-400 italic">
                    No image
                </div>
            </template>

            <!-- Action Buttons -->
            <div
                class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition z-10"
                @click.stop
            >
                <button
                    class="text-blue-600 hover:text-blue-800 bg-white rounded-full p-1 shadow"
                    @click="$emit('edit', medicine)"
                    title="Edit"
                >
                    <font-awesome-icon icon="pen-square" />
                </button>
                <button
                    class="text-red-500 hover:text-red-700 bg-white rounded-full p-1 shadow"
                    :disabled="isDeleting"
                    @click="$emit('delete', medicine)"
                    title="Delete"
                >
                    <font-awesome-icon
                        :icon="isDeleting ? 'spinner' : 'trash'"
                        :spin="isDeleting"
                    />
                </button>
            </div>
        </div>

        <!-- Card Body -->
        <div class="p-4 flex flex-col gap-1">
            <h3 class="text-base font-semibold text-sky-700 line-clamp-2">{{ medicine.name }}</h3>
            <p class="text-sm text-gray-500 line-clamp-2">{{ medicine.description || 'No description' }}</p>
            <p class="text-sm text-gray-700">
                <strong>Stock:</strong> {{ medicine.stock }} {{ medicine.unit }}
            </p>
            <p class="text-sm text-gray-700">
                <strong>Price:</strong> Rp {{ Number(medicine.price).toLocaleString("id-ID") }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watchEffect } from "vue";
import { getMedicineImageBlob } from "@/services/medicine.service";

const props = defineProps<{
    medicine: any;
    isDeleting?: boolean;
}>();
const emit = defineEmits(["edit", "delete"]);

const currentImage = ref(0);
const imageUrls = ref<string[]>([]);

watchEffect(async () => {
    if (props.medicine.images?.length) {
        imageUrls.value = [];
        for (const img of props.medicine.images) {
            const url = await getMedicineImageBlob(img.id);
            if (url) imageUrls.value.push(url);
        }
    }
});

const nextImage = () => {
    currentImage.value = (currentImage.value + 1) % imageUrls.value.length;
};

const prevImage = () => {
    currentImage.value =
        (currentImage.value - 1 + imageUrls.value.length) % imageUrls.value.length;
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
