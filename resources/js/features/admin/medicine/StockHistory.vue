<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useStore } from "vuex";
import {
    getGlobalStockHistory,
    exportGlobalStockHistoryCSV,
} from "@/services/medicine.service";

const store = useStore();
const token = computed(() => store.getters["auth/token"]);

const histories = ref<any[]>([]);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 10;
const isLoading = ref(false);
const isExporting = ref(false);

const filters = ref({
    start_date: "",
    end_date: "",
    type: "",
});

const loadHistories = async () => {
    isLoading.value = true;
    const res = await getGlobalStockHistory(
        currentPage.value,
        perPage,
        filters.value.start_date,
        filters.value.end_date,
        filters.value.type
    );
    histories.value = res.data.data.items;
    totalPages.value = res.data.data.total_pages;
    isLoading.value = false;
};

const exportCSV = async () => {
    isExporting.value = true;
    const blob = await exportGlobalStockHistoryCSV(
        filters.value.start_date,
        filters.value.end_date,
        filters.value.type,
        token.value
    );
    if (blob) {
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "global_stock_history.csv";
        link.click();
    }
    isExporting.value = false;
};

onMounted(loadHistories);
</script>

<template>
    <div class="space-y-6">

        <!-- Filter Toolbar -->
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block font-medium">Start Date</label>
                <input type="date" v-model="filters.start_date" class="border px-3 py-2 rounded" />
            </div>
            <div>
                <label class="block font-medium">End Date</label>
                <input type="date" v-model="filters.end_date" class="border px-3 py-2 rounded" />
            </div>
            <div>
                <label class="block font-medium">Type</label>
                <select v-model="filters.type" class="border px-3 py-2 rounded">
                    <option value="">All</option>
                    <option value="in">IN</option>
                    <option value="out">OUT</option>
                </select>
            </div>
            <button @click="loadHistories" class="bg-sky-500 text-white px-4 py-2 rounded">
                Apply Filter
            </button>
            <button
                @click="exportCSV"
                :disabled="isExporting"
                class="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2"
            >
                <font-awesome-icon icon="file-csv" />
                <span v-if="!isExporting">Export CSV</span>
                <font-awesome-icon v-else icon="spinner" spin />
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded shadow bg-white">
            <table class="min-w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                <tr>
                    <th class="p-3">Date</th>
                    <th class="p-3">Type</th>
                    <th class="p-3">Amount</th>
                    <th class="p-3">Note</th>
                    <th class="p-3">User</th>
                    <th class="p-3">Medicine</th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="(item, index) in histories"
                    :key="index"
                    class="border-b hover:bg-sky-50"
                >
                    <td class="p-3">{{ item.created_at }}</td>
                    <td class="p-3">
                            <span
                                :class="item.type === 'in' ? 'text-green-600' : 'text-red-500'"
                            >
                                {{ item.type.toUpperCase() }}
                            </span>
                    </td>
                    <td class="p-3">{{ item.amount }}</td>
                    <td class="p-3">{{ item.note }}</td>
                    <td class="p-3">{{ item.user_name }}</td>
                    <td class="p-3">{{ item.medicine_name }}</td>
                </tr>
                <tr v-if="!histories.length && !isLoading">
                    <td colspan="6" class="text-center text-gray-400 py-4">
                        No history found
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center gap-4 mt-4">
            <button
                class="px-4 py-2 border rounded"
                :disabled="currentPage === 1"
                @click="() => { currentPage--; loadHistories(); }"
            >
                <font-awesome-icon icon="chevron-left" />
            </button>
            <span class="px-4 py-2">{{ currentPage }} / {{ totalPages }}</span>
            <button
                class="px-4 py-2 border rounded"
                :disabled="currentPage === totalPages"
                @click="() => { currentPage++; loadHistories(); }"
            >
                <font-awesome-icon icon="chevron-right" />
            </button>
        </div>
    </div>
</template>
