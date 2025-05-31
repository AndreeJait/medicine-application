<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { createMedicine, uploadMedicineImages } from "@/services/medicine.service";

const router = useRouter();

const form = ref({
    name: "",
    price: 0,
    unit: "",
    stock: 0,
    description: "",
});

const images = ref<File[]>([]);
const previews = ref<string[]>([]);
const isSubmitting = ref(false);

const validTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif", "image/webp"];

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const selected = Array.from(target.files);
        const filtered = selected.filter((file) => validTypes.includes(file.type));

        if (filtered.length !== selected.length) {
            alert("Some files were skipped because they are not valid image formats.");
        }

        images.value = filtered;

        // Generate previews
        previews.value = filtered.map((file) => URL.createObjectURL(file));
    }
};

const submit = async () => {
    isSubmitting.value = true;

    try {
        const res = await createMedicine({
            request_header: {
                usecase: "create medicine",
                source: "web",
            },
            ...form.value,
        });

        const medicineId = res.data.data.id;

        if (images.value.length > 0) {
            await uploadMedicineImages(medicineId, images.value);
        }

        await router.push("/admin/medicine/list");
    } catch (err) {
        console.error(err);
        alert("Failed to create medicine");
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div class="max-w-2xl mx-auto bg-white shadow p-6 rounded space-y-6">
        <h2 class="text-xl font-bold text-sky-600">Add Medicine</h2>

        <form @submit.prevent="submit" class="space-y-4">
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
                <label class="block font-medium mb-1">Initial Stock</label>
                <input v-model="form.stock" type="number" min="0" required class="w-full border px-3 py-2 rounded" />
            </div>

            <div>
                <label class="block font-medium mb-1">Description (optional)</label>
                <textarea v-model="form.description" rows="3" class="w-full border px-3 py-2 rounded"></textarea>
            </div>

            <div>
                <label class="block font-medium mb-1">Images</label>
                <input type="file" multiple accept="image/*" @change="handleFileChange" />
                <p class="text-sm text-gray-500 mt-1">You can upload multiple JPG/PNG/GIF/WEBP images.</p>

                <!-- Image Preview -->
                <div class="flex gap-3 mt-3 flex-wrap">
                    <div
                        v-for="(src, index) in previews"
                        :key="index"
                        class="w-24 h-24 rounded border overflow-hidden bg-gray-100 flex items-center justify-center"
                    >
                        <img :src="src" alt="Preview" class="object-cover w-full h-full" />
                    </div>
                    <div v-if="previews.length === 0" class="text-sm text-gray-400 italic">
                        No image selected.
                    </div>
                </div>
            </div>

            <button
                type="submit"
                :disabled="isSubmitting"
                class="bg-sky-500 text-white px-6 py-2 rounded hover:bg-sky-600 disabled:opacity-50"
            >
                {{ isSubmitting ? "Submitting..." : "Submit" }}
            </button>
        </form>
    </div>
</template>
