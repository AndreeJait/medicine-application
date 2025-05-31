<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getMedicineById,
    getMedicineImageBlob,
    updateMedicine,
    deleteMedicineImage,
    uploadMedicineImages
} from "@/services/medicine.service";

const route = useRoute();
const router = useRouter();
const medicineId = Number(route.params.id);

const form = ref({
    name: "",
    price: 0,
    unit: "",
    stock: 0,
    description: "",
});

const isSubmitting = ref(false);
const isLoading = ref(true);

const imageList = ref<{ id: number; url: string }[]>([]);
const deletingImageId = ref<number | null>(null);

const newImages = ref<File[]>([]);
const previews = ref<string[]>([]);

const validTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif", "image/webp"];

const handleNewImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        const selected = Array.from(input.files);
        const filtered = selected.filter(file => validTypes.includes(file.type));
        if (filtered.length !== selected.length) {
            alert("Some files were skipped because they are not valid images.");
        }
        newImages.value = filtered;
        previews.value = filtered.map(f => URL.createObjectURL(f));
    }
};

onMounted(async () => {
    try {
        const res = await getMedicineById(medicineId);
        const data = res.data.data;

        form.value = {
            name: data.name,
            price: data.price,
            unit: data.unit,
            stock: data.stock,
            description: data.description,
        };

        for (const image of data.images ?? []) {
            const blob = await getMedicineImageBlob(image.id);
            if (blob) {
                imageList.value.push({ id: image.id, url: blob });
            }
        }
    } catch (err) {
        alert("Failed to load medicine");
    } finally {
        isLoading.value = false;
    }
});

const handleDeleteImage = async (imageId: number) => {
    if (!confirm("Delete this image?")) return;
    deletingImageId.value = imageId;
    try {
        await deleteMedicineImage(imageId);
        imageList.value = imageList.value.filter(img => img.id !== imageId);
    } catch (err) {
        alert("Failed to delete image");
    } finally {
        deletingImageId.value = null;
    }
};

const submit = async () => {
    isSubmitting.value = true;
    try {
        await updateMedicine(medicineId, {
            request_header: {
                usecase: "update medicine",
                source: "web",
            },
            ...form.value,
        });

        if (newImages.value.length > 0) {
            await uploadMedicineImages(medicineId, newImages.value);
        }

        router.push("/admin/medicine/list");
    } catch (err) {
        alert("Update failed");
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div class="max-w-2xl mx-auto bg-white shadow p-6 rounded space-y-6">
        <h2 class="text-xl font-bold text-sky-600">Edit Medicine</h2>

        <div v-if="isLoading" class="text-gray-500">Loading...</div>

        <form v-else @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Name</label>
                <input v-model="form.name" type="text" required class="w-full border px-3 py-2 rounded" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">Price</label>
                    <input v-model="form.price" type="number" min="0" required class="w-full border px-3 py-2 rounded" />
                </div>
                <div>
                    <label class="block font-medium mb-1">Unit</label>
                    <input v-model="form.unit" type="text" required class="w-full border px-3 py-2 rounded" />
                </div>
            </div>

            <div>
                <label class="block font-medium mb-1">Stock</label>
                <input disabled v-model="form.stock" type="number" min="0" required class="w-full border px-3 py-2 rounded" />
            </div>

            <div>
                <label class="block font-medium mb-1">Description</label>
                <textarea v-model="form.description" rows="3" class="w-full border px-3 py-2 rounded" />
            </div>

            <!-- Preview existing images -->
            <div v-if="imageList.length > 0">
                <label class="block font-medium mb-1">Current Images</label>
                <div class="flex flex-wrap gap-4 mt-2">
                    <div
                        v-for="img in imageList"
                        :key="img.id"
                        class="relative group w-24 h-24 border rounded overflow-hidden"
                    >
                        <img :src="img.url" class="object-cover w-full h-full" />
                        <button
                            class="absolute top-1 right-1 text-white bg-red-500 hover:bg-red-600 p-1 rounded-full opacity-0 group-hover:opacity-100 transition"
                            @click.prevent="handleDeleteImage(img.id)"
                            :disabled="deletingImageId === img.id"
                        >
                            <font-awesome-icon
                                :icon="deletingImageId === img.id ? 'spinner' : 'trash'"
                                :spin="deletingImageId === img.id"
                            />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload new images -->
            <div>
                <label class="block font-medium mb-1 mt-4">Add New Images</label>
                <input type="file" multiple accept="image/*" @change="handleNewImageChange" />
                <p class="text-sm text-gray-500 mt-1">Only JPG, PNG, GIF, WEBP allowed</p>

                <!-- Preview new selected images -->
                <div class="flex gap-4 mt-3 flex-wrap" v-if="previews.length">
                    <div
                        v-for="(img, index) in previews"
                        :key="index"
                        class="w-24 h-24 border rounded overflow-hidden"
                    >
                        <img :src="img" class="w-full h-full object-cover" />
                    </div>
                </div>
            </div>

            <button
                type="submit"
                :disabled="isSubmitting"
                class="bg-sky-500 text-white px-6 py-2 rounded hover:bg-sky-600 disabled:opacity-50"
            >
                {{ isSubmitting ? "Updating..." : "Update" }}
            </button>
        </form>
    </div>
</template>
