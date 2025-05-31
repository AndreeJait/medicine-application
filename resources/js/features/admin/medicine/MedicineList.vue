<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import {
    getMedicines,
    getMedicineImageBlob,
    deleteMedicine,
} from "@/services/medicine.service";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import MedicineCard from "@/components/card/MedicineCard.vue";

const router = useRouter();
const store = useStore();

const medicines = ref<any[]>([]);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 9;
const searchQuery = ref("");
const imageMap = ref<Record<number, string>>({});
const imageIndexes = ref<Record<number, number>>({});
const exportLoading = ref(false);
const isLoading = ref(true);
const deletingId = ref<number | null>(null);

const token = computed(() => store.getters["auth/token"]);

const loadMedicines = async () => {
    isLoading.value = true;
    const res = await getMedicines(currentPage.value, perPage, searchQuery.value);
    const result = res.data.data;

    medicines.value = result.items;
    totalPages.value = result.total_pages;
    currentPage.value = result.page;

    for (const med of medicines.value) {
        if (med.images?.length) {
            imageIndexes.value[med.id] = 0;
            for (const image of med.images) {
                if (!imageMap.value[image.id]) {
                    const blob = await getMedicineImageBlob(image.id);
                    if (blob) imageMap.value[image.id] = blob;
                }
            }
        }
    }

    isLoading.value = false;
};

const nextImage = (medId: number, images: any[]) => {
    imageIndexes.value[medId] = (imageIndexes.value[medId] + 1) % images.length;
};

const prevImage = (medId: number, images: any[]) => {
    imageIndexes.value[medId] =
        (imageIndexes.value[medId] - 1 + images.length) % images.length;
};

const handleDelete = async (id: number) => {
    if (!confirm("Are you sure you want to delete this medicine?")) return;
    deletingId.value = id;
    try {
        await deleteMedicine(id);
        await loadMedicines();
    } catch (e) {
        alert("Failed to delete");
    } finally {
        deletingId.value = null;
    }
};

const goToAddMedicine = () => router.push("/admin/medicine/add");

const onSearch = () => {
    currentPage.value = 1;
    loadMedicines();
};

const exportCsv = async () => {
    exportLoading.value = true;
    await store.dispatch("medicine/exportCSV");
    exportLoading.value = false;
};

onMounted(loadMedicines);
</script>

<template>
    <div class="space-y-6">
        <!-- Toolbar -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div class="flex items-center gap-2">
                <input
                    v-model="searchQuery"
                    @keyup.enter="onSearch"
                    placeholder="Search medicine name..."
                    class="border px-3 py-2 rounded w-64"
                />
                <button @click="onSearch" class="bg-sky-500 text-white px-4 py-2 rounded">
                    <font-awesome-icon icon="search" />
                </button>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="goToAddMedicine" class="bg-green-500 text-white px-4 py-2 rounded flex items-center gap-2">
                    <font-awesome-icon icon="plus" />
                    Add Medicine
                </button>
                <button
                    @click="exportCsv"
                    :disabled="exportLoading"
                    class="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
                >
                    <font-awesome-icon icon="file-csv" />
                    <span v-if="!exportLoading">Export CSV</span>
                    <font-awesome-icon v-else icon="spinner" spin />
                </button>
            </div>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- SHIMMER -->
            <div v-if="isLoading" v-for="n in 6" :key="n" class="bg-white shadow rounded p-4 animate-pulse">
                <div class="h-32 bg-gray-200 rounded mb-4"></div>
                <div class="h-4 bg-gray-300 w-3/4 mb-2 rounded"></div>
                <div class="h-4 bg-gray-200 w-1/2 rounded mb-2"></div>
                <div class="h-4 bg-gray-200 w-1/4 mb-4 rounded"></div>
                <div class="h-8 bg-gray-300 w-1/3 rounded"></div>
            </div>

            <!-- MEDICINE CARDS -->
            <MedicineCard
                v-for="med in medicines"
                :key="med.id"
                :medicine="med"
                :is-deleting="deletingId === med.id"
                @edit="router.push(`/admin/medicine/edit/${med.id}`)"
                @delete="handleDelete(med.id)"
            />


            <div v-if="!isLoading && medicines.length === 0" class="col-span-full text-center text-gray-400">
                No medicine found
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center items-center gap-4 mt-6">
            <button
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center gap-2"
                :disabled="currentPage === 1"
                @click="() => { currentPage--; loadMedicines(); }"
            >
                <font-awesome-icon icon="angle-left" />
                Prev
            </button>
            <span class="text-gray-700 font-medium">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
            <button
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center gap-2"
                :disabled="currentPage === totalPages"
                @click="() => { currentPage++; loadMedicines(); }"
            >
                Next
                <font-awesome-icon icon="angle-right" />
            </button>
        </div>
    </div>
</template>

<style scoped>
/* No additional styling needed for now */
</style>
