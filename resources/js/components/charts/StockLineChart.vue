<script setup lang="ts">
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    CategoryScale,
    LinearScale,
    PointElement,
} from "chart.js";

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

defineProps<{
    labels: string[];
    data: number[];
}>();

const chartData = (labels: string[], data: number[]) => ({
    labels,
    datasets: [
        {
            label: "Total Stock",
            data,
            fill: false,
            borderColor: "#0ea5e9",
            tension: 0.4,
        },
    ],
});

const chartOptions = {
    responsive: true,
    plugins: {
        legend: {
            position: "top" as const,
        },
        title: {
            display: false,
        },
    },
};
</script>

<template>
    <Line :data="chartData(labels, data)" :options="chartOptions" />
</template>
