<script setup lang="ts">
import {computed, onMounted, ref} from "vue";
import { getDashboardSummary, getStockChart } from "@/services/dashboard.service";
import StockLineChart from "@/components/charts/StockLineChart.vue";

const summary = ref({
    total_medicine: 0,
    total_stock: 0,
    stock_in: 0,
    stock_out: 0,
});

const activities = ref<any[]>([]);
const topMedicines = ref<any[]>([]);
const stockChart = ref<{ date: string; total_change: number }[]>([]);
const isLoading = ref(true);

onMounted(async () => {
    const [summaryRes, chartRes] = await Promise.all([
        getDashboardSummary(),
        getStockChart(),
    ]);

    const summaryData = summaryRes.data.data;
    const chartData = chartRes.data.data;

    summary.value = {
        total_medicine: summaryData.total_medicine,
        total_stock: parseInt(summaryData.total_stock),
        stock_in: parseInt(summaryData.total_stock_in),
        stock_out: parseInt(summaryData.total_stock_out),
    };
    activities.value = summaryData.recent_activities;
    topMedicines.value = summaryData.top_medicines;
    stockChart.value = chartData ?? [];
    isLoading.value = false;
});

const summaryCards = computed(() => [
    {
        label: "Total Medicine",
        value: summary.value.total_medicine,
        color: "",
    },
    {
        label: "Stock In",
        value: summary.value.stock_in,
        color: "text-green-600",
    },
    {
        label: "Stock Out",
        value: summary.value.stock_out,
        color: "text-red-500",
    },
    {
        label: "Total Stock",
        value: summary.value.total_stock,
        color: "",
    },
]);
</script>

<template>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                v-for="(card, i) in summaryCards"
                :key="i"
                class="p-4 bg-white shadow rounded text-center"
            >
                <p class="text-gray-500">{{ card.label }}</p>
                <p
                    class="text-2xl font-bold"
                    :class="card.color"
                >
                    <span v-if="isLoading" class="block w-1/2 h-6 mx-auto bg-gray-200 rounded animate-pulse"></span>
                    <span v-else>{{ card.value }}</span>
                </p>
            </div>
        </div>
        <!-- Top 5 Medicines -->
        <div class="p-4 bg-white shadow rounded">
            <h3 class="text-lg font-semibold mb-4">Top 5 Medicines</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-2 border">#</th>
                        <th class="p-2 border">Name</th>
                        <th class="p-2 border">Stock</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-if="isLoading"
                        v-for="n in 5"
                        :key="n"
                        class="animate-pulse bg-gray-100"
                    >
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-6"></div></td>
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-32"></div></td>
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-12"></div></td>
                    </tr>
                    <tr
                        v-for="(med, i) in topMedicines"
                        :key="med.id"
                        v-show="!isLoading"
                        class="hover:bg-gray-50"
                    >
                        <td class="p-2 border">{{ i + 1 }}</td>
                        <td class="p-2 border">{{ med.name }}</td>
                        <td class="p-2 border font-bold">{{ med.stock }}</td>
                    </tr>
                    <tr v-if="!isLoading && topMedicines.length === 0">
                        <td colspan="3" class="text-center p-2 text-gray-400">No data</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="p-4 bg-white shadow rounded">
            <h3 class="text-lg font-semibold mb-4">Recent Stock Activities</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-2 border">Date</th>
                        <th class="p-2 border">Medicine</th>
                        <th class="p-2 border">Type</th>
                        <th class="p-2 border">Amount</th>
                        <th class="p-2 border">Note</th>
                        <th class="p-2 border">User</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-if="isLoading"
                        v-for="n in 5"
                        :key="n"
                        class="animate-pulse bg-gray-100"
                    >
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-20"></div></td>
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-32"></div></td>
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-10"></div></td>
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-10"></div></td>
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-32"></div></td>
                        <td class="p-2 border"><div class="h-4 bg-gray-300 rounded w-24"></div></td>
                    </tr>
                    <tr
                        v-for="(activity, i) in activities"
                        :key="i"
                        v-show="!isLoading"
                        class="hover:bg-gray-50"
                    >
                        <td class="p-2 border">{{ activity.created_at }}</td>
                        <td class="p-2 border">{{ activity.medicine_name }}</td>
                        <td class="p-2 border">
                                <span :class="activity.type === 'in' ? 'text-green-600' : 'text-red-500'">
                                    {{ activity.type.toUpperCase() }}
                                </span>
                        </td>
                        <td class="p-2 border">{{ activity.amount }}</td>
                        <td class="p-2 border">{{ activity.note }}</td>
                        <td class="p-2 border">{{ activity.user_name }}</td>
                    </tr>
                    <tr v-if="!isLoading && activities.length === 0">
                        <td colspan="6" class="text-center p-2 text-gray-400">No recent activity</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Stock Movement Chart -->
        <div class="p-4 bg-white shadow rounded">
            <h3 class="text-lg font-semibold mb-4">Stock Movement (Last 7 Days)</h3>
            <div v-if="isLoading" class="h-48 bg-gray-100 animate-pulse rounded"></div>
            <StockLineChart
                v-else
                :labels="stockChart.map(item => item.date)"
                :data="stockChart.map(item => item.total_change)"
            />
        </div>
    </div>
</template>
