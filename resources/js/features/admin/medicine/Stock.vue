<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import {
    getMedicineDetail,
    getMedicineImageBlob,
    getStockHistory,
    stockIn,
    stockOut,
    exportStockHistoryCSV,
} from "@/services/medicine.service";

const route = useRoute();
const medicineId = Number(route.params.id);

const medicine = ref<any>(null);
const imageUrls = ref<string[]>([]);
const currentImage = ref(0);
const histories = ref<any[]>([]);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 10;
const isLoading = ref(false);
const isExporting = ref(false);
const stockModal = ref<{ show: boolean; type: "in" | "out"; amount: number; note: string }>({
    show: false,
    type: "in",
    amount: 0,
    note: "",
});
const dateRange = ref<{ start: string; end: string }>({
    start: "",
    end: "",
});

const isSubmittingStock = ref(false);

const loadMedicineDetail = async () => {
    const res = await getMedicineDetail(medicineId);
    medicine.value = res.data.data;

    if (medicine.value.images?.length) {
        const urls: string[] = [];
        for (const img of medicine.value.images) {
            const blob = await getMedicineImageBlob(img.id);
            if (blob) urls.push(blob);
        }
        imageUrls.value = urls;
    }
};

const loadStockHistory = async () => {
    isLoading.value = true;
    const res = await getStockHistory(
        medicineId,
        currentPage.value,
        perPage,
        dateRange.value.start,
        dateRange.value.end
    );

    histories.value = res.data.data.items;
    currentPage.value = res.data.data.page;
    totalPages.value = res.data.data.total_pages;
    isLoading.value = false;
};

const submitStock = async () => {
    if (isSubmittingStock.value) return;

    isSubmittingStock.value = true;
    const apiCall = stockModal.value.type === "in" ? stockIn : stockOut;

    try {
        await apiCall(medicineId, stockModal.value.amount, stockModal.value.note);
        stockModal.value = { show: false, type: "in", amount: 0, note: "" };
        await loadMedicineDetail();
        await loadStockHistory();
    } catch (err) {
        alert("Failed to update stock");
    } finally {
        isSubmittingStock.value = false;
    }
};

const handleExport = async () => {
    isExporting.value = true;
    const blob = await exportStockHistoryCSV(
        medicineId,
        dateRange.value.start,
        dateRange.value.end
    );
    if (blob) {
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "stock_history.csv";
        link.click();
    }
    isExporting.value = false;
};

const prevImage = () => {
    currentImage.value = (currentImage.value - 1 + imageUrls.value.length) % imageUrls.value.length;
};
const nextImage = () => {
    currentImage.value = (currentImage.value + 1) % imageUrls.value.length;
};

onMounted(async () => {
    await loadMedicineDetail();
    await loadStockHistory();
});
</script>

<template>
    <div class="space-y-6">

        <!-- Layout: Detail & History -->
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Medicine Detail -->
            <div class="bg-white rounded shadow p-4 space-y-4">
                <h3 class="text-lg font-semibold">Medicine Info</h3>
                <div class="relative h-48 bg-gray-100 rounded overflow-hidden">
                    <template v-if="imageUrls.length">
                        <img
                            :src="imageUrls[currentImage]"
                            class="w-full h-full object-cover"
                            alt="Medicine"
                        />
                        <span
                            v-if="imageUrls.length > 1"
                            class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-0.5 rounded"
                        >
                            {{ currentImage + 1 }} / {{ imageUrls.length }}
                        </span>
                        <div class="absolute inset-0 flex justify-between items-center px-2">
                            <button @click.stop="prevImage" class="text-white bg-black/40 p-1 rounded-full">‹</button>
                            <button @click.stop="nextImage" class="text-white bg-black/40 p-1 rounded-full">›</button>
                        </div>
                    </template>
                    <template v-else>
                        <div class="w-full h-full flex items-center justify-center text-gray-400 italic">
                            No image
                        </div>
                    </template>
                </div>
                <div>
                    <p><strong>Name:</strong> {{ medicine?.name }}</p>
                    <p><strong>Stock:</strong> {{ medicine?.stock }}</p>
                    <p><strong>Unit:</strong> {{ medicine?.unit }}</p>
                    <p><strong>Price:</strong> Rp {{ Number(medicine?.price).toLocaleString("id-ID") }}</p>
                    <p><strong>Description:</strong> {{ medicine?.description || "-" }}</p>
                </div>
                <div class="flex gap-2">
                    <button
                        class="bg-green-600 text-white px-4 py-2 rounded flex items-center gap-2"
                        @click="stockModal = { show: true, type: 'in', amount: 0, note: '' }"
                    >
                        <font-awesome-icon icon="arrow-up" />
                        Stock In
                    </button>
                    <button
                        class="bg-red-500 text-white px-4 py-2 rounded flex items-center gap-2"
                        @click="stockModal = { show: true, type: 'out', amount: 0, note: '' }"
                    >
                        <font-awesome-icon icon="arrow-down" />
                        Stock Out
                    </button>
                </div>
            </div>

            <!-- History Table -->
            <div class="md:col-span-2 space-y-4">
                <div class="bg-white p-4 rounded shadow space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <label>Start:</label>
                            <input type="date" v-model="dateRange.start" class="border px-2 py-1 rounded" />
                            <label>End:</label>
                            <input type="date" v-model="dateRange.end" class="border px-2 py-1 rounded" />

                            <button
                                @click="loadStockHistory"
                                class="bg-sky-600 text-white px-4 py-1.5 rounded hover:bg-sky-700 transition"
                            >
                                <font-awesome-icon icon="filter" class="mr-1" />
                                Apply Filter
                            </button>
                        </div>

                        <button
                            @click="handleExport"
                            :disabled="isExporting"
                            class="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
                        >
                            <font-awesome-icon icon="file-csv" />
                            <span v-if="!isExporting">Export CSV</span>
                            <font-awesome-icon v-else icon="spinner" spin />
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border">
                            <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="p-2 border">Date</th>
                                <th class="p-2 border">Type</th>
                                <th class="p-2 border">Amount</th>
                                <th class="p-2 border">Note</th>
                                <th class="p-2 border">User</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="item in histories" :key="item.id" class="hover:bg-gray-50">
                                <td class="p-2 border">{{ item.created_at }}</td>
                                <td class="p-2 border">
                                    <span :class="item.type === 'in' ? 'text-green-600' : 'text-red-500'">
                                        {{ item.type.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="p-2 border">{{ item.amount }}</td>
                                <td class="p-2 border">{{ item.note }}</td>
                                <td class="p-2 border">{{ item.user_name }}</td>
                            </tr>
                            <tr v-if="histories.length === 0 && !isLoading">
                                <td colspan="5" class="text-center text-gray-400 p-2">No history</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-center gap-4 mt-2">
                        <button
                            class="px-3 py-1 border rounded"
                            :disabled="currentPage === 1"
                            @click="() => { currentPage--; loadStockHistory(); }"
                        >
                            <font-awesome-icon icon="chevron-left" />
                        </button>
                        <span class="px-3 py-1">{{ currentPage }} / {{ totalPages }}</span>
                        <button
                            class="px-3 py-1 border rounded"
                            :disabled="currentPage === totalPages"
                            @click="() => { currentPage++; loadStockHistory(); }"
                        >
                            <font-awesome-icon icon="chevron-right" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Modal -->
        <div v-if="stockModal.show" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded shadow w-full max-w-md space-y-4">
                <h3 class="text-lg font-semibold">
                    {{ stockModal.type === 'in' ? 'Add Stock' : 'Remove Stock' }}
                </h3>
                <div>
                    <label class="block font-medium">Amount</label>
                    <input v-model="stockModal.amount" type="number" min="1" class="w-full border px-3 py-2 rounded" />
                </div>
                <div>
                    <label class="block font-medium">Note</label>
                    <textarea v-model="stockModal.note" class="w-full border px-3 py-2 rounded" rows="2"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="stockModal.show = false" class="px-4 py-2 border rounded">Cancel</button>
                    <button
                        :disabled="isSubmittingStock"
                        @click="submitStock"
                        class="bg-sky-500 text-white px-4 py-2 rounded flex items-center gap-2 disabled:opacity-50"
                    >
                        <font-awesome-icon v-if="isSubmittingStock" icon="spinner" spin />
                        <span>{{ isSubmittingStock ? 'Submitting...' : 'Submit' }}</span>
                    </button>

                </div>
            </div>
        </div>
    </div>
</template>
